/**
 * Unilabel type imageboard
 *
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import log from 'core/log';

export const init = () => {
    // Create an array selectedImage to be able to store some data about the selected image that is moved.
    let selectedImage = {};
    selectedImage.number = null;
    selectedImage.number = null;
    selectedImage.src = '';
    // ItemToMove is the div that the selected image is inside AND the title. We do NOT move the image we move the itemtomove-div.
    selectedImage.itemToMove = null;
    // ToDo: Add documentation about xoffset?
    selectedImage.eventlayerX = 0;
    selectedImage.eventlayerY = 0;
    selectedImage.width = null;
    selectedImage.height = null;
    selectedImage.titlecorrectorX = 0;
    selectedImage.titlecorrectorY = 0;

    // Store the data about the canvas/background.
    let canvas = null;
    let canvaswidth = 600;
    let canvasheight = 400;

    registerDnDListener();

    /**
     *  We need two event listeners for drag and drop. One when the dragging starts and one when it ends.
     */
    function registerDnDListener() {
        refreshCanvasSize();
        canvas.addEventListener("dragstart", dragStart, false);
        canvas.addEventListener("dragend", dragEnd, false);
        document.addEventListener("canvaschanged", refreshCanvasSize);
    }

    /**
     * Update canvas size. So drag and drop has new boundaries.
     */
    function refreshCanvasSize() {
        canvas = document.getElementById("unilabel-imageboard-background-canvas");
        canvaswidth = canvas.clientWidth;
        canvasheight = canvas.clientHeight;
    }

    /**
     *
     * @param {event} event
     */
    function dragStart(event) {
        console.log("event.target.getAttribute('id').split('edit-icon-')[1]",
            event.target.getAttribute('id').split('edit-icon-')[1]);
        if (event.target &&
            event.target.getAttribute('id') &&
            event.target.getAttribute('id').split('edit-icon-')[1] >= 1) {
            console.log("Icon nummer=", event.target.getAttribute('id').split('edit-icon-')[1]);
            return;
        }
        // Check if title or image is selected because this leads to different offsets.
        // We have to do different calculation of x and y position.
        let selectedType = "nix";
        if (event &&
            event.explicitOriginalTarget &&
            event.explicitOriginalTarget.classList &&
            event.explicitOriginalTarget.classList.contains('unilabel-imageboard-image')) {
            selectedType = "image";
        } else {
            selectedType = "title";
        }
        // ToDo: Check this condition ....  shouldnt it check         selectedType = "image"
        if (event && event.target && event.target.classList.contains('unilabel-imageboard-element-draggable')) {
            // Image was selected, so we have to store the information about this image.
            // 1. Get the number of the selected element.
            let number = event.target.getAttribute('id').split('unilabel-imageboard-element-')[1];
            // 2. Get imagedata of the selected element.
            let imagedata = getAllImagedataFromForm(number);
            // 3. Set the number of the selected image so this image can be updated when dragEnds.
            selectedImage.number = number;
            // 4. Collect the other information.
            selectedImage.title = imagedata.title;
            selectedImage.titlelineheight = imagedata.titlelineheight;
            selectedImage.fontsize = imagedata.fontsize;
            selectedImage.width = imagedata.targetwidth;
            selectedImage.height = imagedata.targetheight;
            selectedImage.itemToMove = document.getElementById('unilabel-imageboard-element-' + selectedImage.number);
            // Attention: layerX and layerY is the relative position of the mouseposition inside div.
            // So div is the image or the title and the layer depends on this according to the complete element.
            selectedImage.eventlayerX = event.layerX;
            selectedImage.eventlayerY = event.layerY;
            if (selectedType == "title") {
                // If a title is selected then the position is relative to the upper left corner of the title. Thus we
                // have to use a correction-value because we store the coordinates that belong to the image.
                selectedImage.titlecorrectorY = selectedImage.fontsize * selectedImage.titlelineheight;
            } else {
                selectedImage.titlecorrectorY = 0;
            }
        }
    }

    /**
     * At the end of drag update the inputfield and set the coordinates into the attribute of the image.
     *
     * @param {event} event
     */
    function dragEnd(event) {
        let snap = 1;
        let snapelement = document.getElementById('unilabeltype-imageboard-snap');
        if (snapelement !== null && Number.isInteger(Number(snapelement.value))) {
            snap = snapelement.value;
        }

        if (event.target &&
            event.target.getAttribute('id') &&
            event.target.getAttribute('id').split('edit-icon-')[1] >= 1) {
            console.log("Icon nummerrr=", event.target.getAttribute('id').split('edit-icon-')[1]);
            return;
        }
        marktargetasselected(event.target);

        if (selectedImage.number !== null) {
            showSettingsOfImage(selectedImage.number);
            // Information: snap is an integer and allows to use an snapping grid.
            // xposition = 123 with snap 10 will be calculated to 120.
            // xposition = 123 with snap 100 will be calculated to 10.
            let xposition = calculateXposition(event, snap);
            let yposition = calculateYposition(event, snap);
            selectedImage.itemToMove.style.left = xposition + selectedImage.titlecorrectorX + "px";
            selectedImage.itemToMove.style.top = parseInt(yposition) + parseInt(selectedImage.titlecorrectorY) + "px";

            // Change the inputfield.
            const inputPositionX = document.getElementById('id_unilabeltype_imageboard_xposition_' + (selectedImage.number));
            inputPositionX.value = xposition;

            const inputPositionY = document.getElementById('id_unilabeltype_imageboard_yposition_' + (selectedImage.number));
            inputPositionY.value = parseInt(yposition) + parseInt(selectedImage.titlecorrectorY);

            // ToDo: duplicate code for update coordinates
            let coordinates = document.getElementById('unilabel-imageboard-coordinates');
            coordinates.innerHTML = (parseInt(selectedImage.number) + 1) + ": " +
                inputPositionX.value + " / " + inputPositionY.value;

            // Reset saved image data
            selectedImage.number = null;
            selectedImage.titlecorrectorY = 0;
        }
    }

    /**
     *
     * @param {target} target
     */
    function marktargetasselected(target) {
        console.log("marktargetasselected");
        console.log("target", target);
        let technicalnumber = target.getAttribute('id').split('unilabel-imageboard-element-')[1];
        const singleElements = document.querySelectorAll('[id^="fitem_id_unilabeltype_imageboard_image_"]');
        for (let i = 0; i < singleElements.length; i++) {
            // TODO: Skip removed elements that are still in the dom but hidden.
            let singleElement = singleElements[i].getAttribute('id');
            let number = singleElement.split('fitem_id_unilabeltype_imageboard_image_')[1];
            if (number) {
                let image = document.getElementById('unilabel-imageboard-imageid-' + number);
                if (image) {
                    image.classList.remove("selected");
                }
                let title = document.getElementById('id_elementtitle-' + number);
                if (title) {
                    title.classList.remove("selected");
                }
            }
        }
        let selectedimage = document.getElementById('unilabel-imageboard-imageid-' + technicalnumber);
        selectedimage.classList.add("selected");
        let selectedtitle = document.getElementById('id_elementtitle-' + technicalnumber);
        selectedtitle.classList.add("selected");
    }

    /**
     * Hides all setting of elements by adding d-none and removes d-none only for element with the specified number.
     * @param {int} number
     */
    function showSettingsOfImage(number) {
        // In order do know how many elements are existing in the imageboard we search for
        // fitem_id_unilabeltype_imageboard_title_ . The length tells us how many elements exists.
        const singleElements = document.querySelectorAll('[id^="fitem_id_unilabeltype_imageboard_title_"]');
        for (let i = 0; i < singleElements.length; i++) {
            let wrapperOfElement = getWrapper(i);
            if (wrapperOfElement && number == i) {
                // If it is the selected element we have to remove display none (bootstrap class d-none).
                wrapperOfElement.classList.remove('d-none');
            } else {
                // We will hide all other element settings.
                wrapperOfElement.classList.add('d-none');
            }
        }
    }

    /**
     * This function looks for an element in the dom that belongs to an given id and then returns the
     * surrounding wrapper div.
     *
     * @param {number} number
     * @returns {*}
     */
    function getWrapper(number) {
        console.log("getWrapper number=", number);
        let element = document.getElementById('fitem_id_unilabeltype_imageboard_title_' + number);
        console.log("element =", element);
        let wrapperElement = element.closest(".elementwrapper");
        if (wrapperElement) {
            return wrapperElement;
        }
        return null;
    }

    /**
     * Get all data from image that is stored in the form and collects them in one array.
     *
     * @param {int} number of the image
     * @returns {*[]} Array with the collected information that are set in the form for the image.
     */
    function getAllImagedataFromForm(number) {
        let imageids = {
            title: 'id_unilabeltype_imageboard_title_' + number,
            titlecolor: 'id_unilabeltype_imageboard_titlecolor_colourpicker',
            titlebackgroundcolor: 'id_unilabeltype_imageboard_titlebackgroundcolor_colourpicker',
            titlelineheight: 'id_unilabeltype_imageboard_titlelineheight',
            fontsize: 'id_unilabeltype_imageboard_fontsize',
            xposition: 'id_unilabeltype_imageboard_xposition_' + number,
            yposition: 'id_unilabeltype_imageboard_yposition_' + number,
            targetwidth: 'id_unilabeltype_imageboard_targetwidth_' + number,
            targetheight: 'id_unilabeltype_imageboard_targetheight_' + number,
            src: '',
            border: 'id_unilabeltype_imageboard_border_' + number,
            borderradius: 'id_unilabeltype_imageboard_borderradius_' + number,
        };

        let imagedata = {};
        imagedata.title = document.getElementById(imageids.title).value;
        imagedata.titlecolor = document.getElementById(imageids.titlecolor).value;
        imagedata.titlebackgroundcolor = document.getElementById(imageids.titlebackgroundcolor).value;
        imagedata.titlelineheight = document.getElementById(imageids.titlelineheight).value;
        imagedata.fontsize = document.getElementById(imageids.fontsize).value;
        imagedata.xposition = document.getElementById(imageids.xposition).value;
        imagedata.yposition = document.getElementById(imageids.yposition).value;
        imagedata.targetwidth = document.getElementById(imageids.targetwidth).value;
        imagedata.targetheight = document.getElementById(imageids.targetheight).value;

        // Src der Draftfile ermitteln.
        const element = document.getElementById('id_unilabeltype_imageboard_image_' + number + '_fieldset');
        const imagetag = element.getElementsByTagName('img');
        let src = '';
        if (imagetag.length && imagetag.length != 0) {
            src = imagetag[0].src;
            src = src.split('?')[0];
        }
        imagedata.src = src;
        imagedata.border = document.getElementById(imageids.border).value;
        imagedata.borderradius = document.getElementById(imageids.borderradius).value;

        return imagedata;
    }

    /**
     *
     * @param {event} event
     * @param {int} snap
     * @returns {number}
     */
    function calculateXposition(event, snap) {
        let canvasboundings = canvas.getBoundingClientRect();
        let xposition = event.clientX - canvasboundings.left - selectedImage.eventlayerX;

        if (xposition < 0) {
            xposition = 0;
        }
        if (xposition >= canvaswidth - selectedImage.width) {
            xposition = canvaswidth - selectedImage.width;
        }
        return Math.round(xposition / snap) * snap;
    }

    /**
     *
     * @param {event} event
     * @param {int} snap
     * @returns {number}
     */
    function calculateYposition(event, snap) {
        var canvasboundings = canvas.getBoundingClientRect();
        var yposition = event.clientY - canvasboundings.top - selectedImage.eventlayerY;
        if (yposition < 0) {
            yposition = 0;
        }
        if (yposition >= canvasheight - selectedImage.height) {
            yposition = canvasheight - selectedImage.height;
        }
        return Math.round(yposition / snap) * snap;
    }
};
