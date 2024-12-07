/**
 * Unilabel type imageboard
 *
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import * as Str from 'core/str';
import log from 'core/log';

export const init = (canvaswidth, canvasheight, gridcolor, xsteps, ysteps) => {
    canvaswidth = parseInt(canvaswidth, 10);
    canvasheight = parseInt(canvasheight, 10);
    xsteps = parseInt(xsteps, 10);
    ysteps = parseInt(ysteps, 10);
    registerAllEventlistener();
    // Timeout notwendig, damit das Bild in der Draftarea "vorhanden" ist.
    // document.querySelector('#id_unilabeltype_imageboard_backgroundimage_fieldset .filemanager-container .realpreview');
    setTimeout(refreshBackgroundImage, 1000);
    // To show all images on pageload.
    setTimeout(refreshAllImages, 1000);
    setTimeout(function() {
        renderHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);
    }, 1000);

    // In preview only ONE helpergrid exists with number 0...
    const gridtoggler = document.getElementById("unilabeltype-imageboard-gridtoggler-0");
    const togglerText = gridtoggler.querySelector('.unilabeltype-imageboard-toggle-text');
    gridtoggler.addEventListener("click", function(event) {
        const helpergrid = document.getElementById("unilabeltype-imageboard-helpergrid-0");
        event.stopPropagation();
        event.preventDefault();
        if (helpergrid.classList.contains("hidden")) {
            showGrid(togglerText, helpergrid);
        } else {
            hideGrid(togglerText, helpergrid);
        }
    });

    /**
     * Helper function to show the grid from imageboard.
     *
     * @param {object} button
     * @param {object} helpergrid
     */
    function showGrid(button, helpergrid) {
        helpergrid.classList.remove("hidden");
        button.value = 'gridvisible';
        Str.get_string('buttonlabelhelpergridhide', 'unilabeltype_imageboard').done(function(text) {
            button.innerText = text;
        });
    }

    /**
     * Helper function to remove the grid from imageboard.
     *
     * @param {object} button
     * @param {object} helpergrid
     */
    function hideGrid(button, helpergrid) {
        helpergrid.classList.add("hidden");
        button.value = 'gridhidden';
        Str.get_string('buttonlabelhelpergridshow', 'unilabeltype_imageboard').done(function(text) {
            button.innerText = text;
        });
    }

    /**
     *
     * @param {event} event
     */
    function focusoutExecute(event) {
        var number = getNumberFromEvent(event);
        if (number >= 0) {
            refreshImage(number);
        } else {
            // ToDo: only refresh if titlecolor, titlebackgroundcolor, titlesize was changed
            refreshAllImages();
        }
    }

    /**
     *
     * @param {event} event
     * @returns {*}
     */
    function getNumberFromEvent(event) {
        // If there is a focusout event from one of the following input fields then evaluate
        // the number of the element that was changed.
        let imageidselectors = [
            'id_unilabeltype_imageboard_title_',
            'id_unilabeltype_imageboard_alt_',
            'id_unilabeltype_imageboard_xposition_',
            'id_unilabeltype_imageboard_yposition_',
            'id_unilabeltype_imageboard_targetwidth_',
            'id_unilabeltype_imageboard_targetheight_',
            'id_unilabeltype_imageboard_border_',
            'id_unilabeltype_imageboard_borderradius_',
        ];
        const eventid = event.target.getAttribute('id');
        for (let i = 0; i < imageidselectors.length; i++) {
            if (eventid.includes(imageidselectors[i])) {
                return eventid.split(imageidselectors[i])[1];
            }
        }
        // If focus out was NOT from one of our inputfield then return a number less than zero.
        return -1;
    }

    /**
     *
     * @param {event} event
     */
    function onclickExecute(event) {
        var targetid = event.target.getAttribute('id');
        var mform = targetid.split('button-mform1')[1];
        if (mform) {
            setTimeout(function() {
                // An element was added so we have to add a div for the image to the dom.
                let singleElements = document.querySelectorAll('[id^="fitem_id_unilabeltype_imageboard_title_"]');
                let number = singleElements.length;
                addImageToDom(number - 1);
            }, 500);
        }
    }

    /**
     * Register eventlistener to the all input fields of the form to register
     * focus-out events from input fields in order to trigger a fresh of the preview.
     */
    function registerAllEventlistener() {
        var mform = document.querySelectorAll('[id^="mform"]')[0];
        // We register one listener per eventtype to the mform and use the bubble-event-feature to check out
        // the target of an event.

        // All focusout-events will be handeled by oneListenerForAllInputFocusout.
        mform.addEventListener("focusout", focusoutExecute, false);

        // All click-events will be handeled by oneListenerForAllInputClick.
        mform.addEventListener("click", onclickExecute, false);

        // All uploadCompleted-events
        // mform.addEventListener(eventTypes.uploadCompleted, machwas, false);

        // First: When uploading a backgroundimage the backgroundimage of the backgroundimagediv must be updated.
        // ToDo: better use eventlistener
        let backgroundfileNode = document.getElementById('id_unilabeltype_imageboard_backgroundimage_fieldset');
        if (backgroundfileNode) {
            let observer = new MutationObserver(refreshBackgroundImage);
            observer.observe(backgroundfileNode, {attributes: true, childList: true, subtree: true});
        }
        // Also add listener for canvas size
        let canvasx = document.getElementById('id_unilabeltype_imageboard_canvaswidth');
        if (canvasx) {
            let observer = new MutationObserver(refreshBackgroundImage);
            observer.observe(canvasx, {attributes: true, childList: true, subtree: true});
        }
        let canvasy = document.getElementById('id_unilabeltype_imageboard_canvasheight');
        if (canvasy) {
            let observer = new MutationObserver(refreshBackgroundImage);
            observer.observe(canvasy, {attributes: true, childList: true, subtree: true});
        }
    }

    /**
     * Sets the background image of the SVG to the current image in filemanager.
     */
    function refreshBackgroundImage() {
        let filemanagerbackgroundimagefieldset = document.getElementById('id_unilabeltype_imageboard_backgroundimage_fieldset');
        let previewimage = filemanagerbackgroundimagefieldset.getElementsByClassName('realpreview');
        let backgrounddiv = document.getElementById('unilabel-imageboard-background-canvas');

        if (previewimage.length > 0) {
            let backgroundurl = previewimage[0].getAttribute('src').split('?')[0];
            // If the uploaded file reuses the filename of a previously uploaded image, they differ
            // only in the oid. So one has to append the oid to the url.
            if (previewimage[0].getAttribute('src').split('?')[1].includes('&oid=')) {
                backgroundurl += '?oid=' + previewimage[0].getAttribute('src').split('&oid=')[1];
            }
            backgrounddiv.style.background = 'red'; // ToDo: Do wie need this code? Just to indicate changes during dev.
            backgrounddiv.style.backgroundSize = 'cover';
            backgrounddiv.style.backgroundImage = "url('" + backgroundurl + "')";

            const canvaswidthinput = document.getElementById('id_unilabeltype_imageboard_canvaswidth');
            let canvaswidthselected = canvaswidthinput.selectedOptions;
            let canvaswidth = canvaswidthselected[0].value;
            backgrounddiv.style.width = canvaswidth + "px";

            const canvasheightinput = document.getElementById('id_unilabeltype_imageboard_canvasheight');
            let canvasheightselected = canvasheightinput.selectedOptions;
            let canvasheight = canvasheightselected[0].value;
            backgrounddiv.style.height = canvasheight + "px";
        } else {
            // Image might be deleted so update the backroundidv and remove backgroundimage in preview;
            // ToDo    if (previewimage.length > 0) does not recognize when an image is deleted so we need a different condition!
            backgrounddiv.style.background = 'green'; // Todo: check if this is needed. just to indicate changes during development.
            backgrounddiv.style.backgroundImage = "url('')";
            const canvaswidthinput = document.getElementById('id_unilabeltype_imageboard_canvaswidth');
            let canvaswidthselected = canvaswidthinput.selectedOptions;
            let canvaswidth = canvaswidthselected[0].value;
            backgrounddiv.style.width = canvaswidth + "px";

            const canvasheightinput = document.getElementById('id_unilabeltype_imageboard_canvasheight');
            let canvasheightselected = canvasheightinput.selectedOptions;
            let canvasheight = canvasheightselected[0].value;
            backgrounddiv.style.height = canvasheight + "px";
        }
    }


    /**
     *
     * @param {number} canvaswidth
     * @param {number} canvasheight
     * @param {string} gridcolor
     * @param {number} xsteps
     * @param {number} ysteps
     */
    function renderHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps) {
        let helpergrids = [];
        for (let y = 0; y < canvasheight; y = y + ysteps) {
            for (let x = 0; x < canvaswidth; x = x + xsteps) {
                let helpergrid = {};
                helpergrid.x = x;
                helpergrid.y = y;
                helpergrids.push(helpergrid);
            }
        }
        // In preview only one helpergrid exists .... we use cmid = 0.
        const context = {
            // Data to be rendered
            helpergrids: helpergrids,
            gridcolor: gridcolor,
            xsteps: xsteps,
            ysteps: ysteps,
            cmid: 0,
            hidden: 0
        };

        Templates.renderForPromise('unilabeltype_imageboard/imageboard_helpergridpreview', context).then(({html, js}) => {
            // We have to get the actual content, combine it with the rendered image and replace then the actual content.
            let imageboardcontainer = document.getElementById('imageboardcontainer').innerHTML;
            let combined = "<div>" + imageboardcontainer + "</div>" + html;
            Templates.replaceNodeContents('#imageboardcontainer', combined, js);
            // ToDo: Check.
            return;
        }).catch(() => {
            log.debug('Rendering failed');
        });
    }


    /**
     * Gets the number of ALL elements in the form and then adds a div for each element to the dom if not already exists.
     * We need a timeout
     */
    function refreshAllImages() {
        const singleElements = document.querySelectorAll('[id^="fitem_id_unilabeltype_imageboard_image_"]');
        for (let i = 0; i < singleElements.length; i++) {
            // Todo: Skip removed elements that are still in the dom but hidden.
            let singleElement = singleElements[i].getAttribute('id');
            let number = singleElement.split('fitem_id_unilabeltype_imageboard_image_')[1];
            // Check if there exists already a div for this image.
            const imageid = document.getElementById('unilabel-imageboard-imageid-' + number);
            if (imageid === null) {
                // Div does not exist so we need do add it do dom.
                addImageToDom(number);
                // ToDo: Do we need a timeout to wait until the dic was added so that refresh can work correctly?
                // see also refreshImage ... there is already a timeout
                setTimeout(function() {
                    refreshImage(number);
                }, 1000);
            } else {
                refreshImage(number);
            }
        }
    }

    /**
     *
     * @param {int} number
     */
    function addImageToDom(number) {
        const imageid = document.getElementById('unilabel-imageboard-imageid-' + number);
        if (imageid === null) {
            renderAddedImage(number);
            // This div does not exist so we need do add it do dom.
            // Add an obverser to be able to update if image is uploaded.
            let imagefileNode = document.getElementById('fitem_id_unilabeltype_imageboard_image_' + (number));
            if (imagefileNode) {
                let observer = new MutationObserver(refreshImage);
                observer.observe(imagefileNode, {attributes: true, childList: true, subtree: true});
            }
        } else {
            // Div already exists so we need only to refresh the image because we only uploaded a new image
            // to an already existing div.
            refreshImage(number);
        }
    }

    /**
     *
     * @param {number} number of
     */
    function renderAddedImage(number) {
        const context = {
            // Data to be rendered
            number: number,
            title: "title"
        };

        Templates.renderForPromise('unilabeltype_imageboard/previewimage', context).then(({html, js}) => {
            // We have to get the actual content, combine it with the rendered image and replace then the actual content.
            let imageboardcontainer = document.getElementById('imageboardcontainer').innerHTML;
            let combined = "<div>" + imageboardcontainer + "</div>" + html;
            Templates.replaceNodeContents('#imageboardcontainer', combined, js);
            return;
        }).catch(() => {
            // No tiny editor present.
        });
    }

    /**
     * If an image was uploaded or inputfields in the form changed then we need to refresh
     * this image.
     * @param {int} number
     */
    function refreshImage(number) {
        // When there was an upload, then the number is NOT a number.
        // ToDo: Do not yet know the best way how I will get the number in his case.
        // For now if it is a number the normal refresh can be used and only ONE image will be refreshed.
        // In the else code ther will be a refresh of ALL images until I can refactor this.
        if (!Array.isArray(number)) {
            let imageid = document.getElementById("unilabel-imageboard-imageid-" + number);
            // Fill all the needed values for imagedata.
            let imagedata = getAllImagedataFromForm(number);
            imageid.style.background = imagedata.titlebackgroundcolor;
            imageid.src = imagedata.src;

            if (imagedata.src === "") {
                // Hide the image div.
                imageid.classList.add("hidden");
            } else {
                imageid.classList.remove("hidden");
                imageid.alt = imagedata.alt;
            }

            const imagediv = document.getElementById('unilabel-imageboard-element-' + number);
            imagediv.style.left = parseInt(imagedata.xposition) + "px";
            imagediv.style.top = parseInt(imagedata.yposition) + "px";

            // Switch to the correct class eg "unilable-imageboard-titlelineheight-4 if lineheight = 4.
            const idelementtitle = document.getElementById('id_elementtitle-' + number);
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-0");
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-1");
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-2");
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-3");
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-4");
            idelementtitle.classList.remove("unilable-imageboard-titlelineheight-5");
            const dummy = "unilable-imageboard-titlelineheight-" + imagedata.titlelineheight;
            idelementtitle.classList.add(dummy);

            if (imagedata.targetwidth != 0) {
                imageid.style.width = imagedata.targetwidth + "px";
            } else {
                imageid.style.width = "auto";
            }
            if (imagedata.targetheight != 0) {
                imageid.style.height = imagedata.targetheight + "px";
            } else {
                imageid.style.height = "auto";
            }
            if (imagedata.title != "") {
                imageid.title = (parseInt(number) + 1) + ": " + imagedata.title;
            } else {
                imageid.title = (parseInt(number) + 1) + ": ";
            }
            if (imagedata.border != 0) {
                imageid.style.border = imagedata.border + "px solid";
                imageid.style.borderColor = imagedata.titlebackgroundcolor;
            } else {
                imageid.style.border = "0";
            }
            if (imagedata.borderradius != 0) {
                imageid.style.borderRadius = imagedata.borderradius + "px";
            } else {
                imageid.style.borderRadius = "0";
            }

            // Title above image.
            const elementtitle = document.getElementById('id_elementtitle-' + number);
            elementtitle.innerHTML = imagedata.title;
            elementtitle.style.color = imagedata.titlecolor;
            elementtitle.style.backgroundColor = imagedata.titlebackgroundcolor;
            elementtitle.style.fontSize = imagedata.fontsize + "px";
            elementtitle.style.borderRadius = imagedata.borderradius + "px";
        } else {
            setTimeout(function() {
                refreshAllImages();
            }, 600);
        }
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
            alt: 'id_unilabeltype_imageboard_alt_' + number,
            xposition: 'id_unilabeltype_imageboard_xposition_' + number,
            yposition: 'id_unilabeltype_imageboard_yposition_' + number,
            targetwidth: 'id_unilabeltype_imageboard_targetwidth_' + number,
            targetheight: 'id_unilabeltype_imageboard_targetheight_' + number,
            src: '',
            border: 'id_unilabeltype_imageboard_border_' + number,
            borderradius: 'id_unilabeltype_imageboard_borderradius_' + number,
            coordinates: "unilabel-imageboard-coordinates-" + number,
        };

        let imagedata = {};
        imagedata.title = document.getElementById(imageids.title).value;
        imagedata.titlecolor = document.getElementById(imageids.titlecolor).value;
        imagedata.titlebackgroundcolor = document.getElementById(imageids.titlebackgroundcolor).value;
        imagedata.titlelineheight = document.getElementById(imageids.titlelineheight).value;
        imagedata.fontsize = document.getElementById(imageids.fontsize).value;
        imagedata.alt = document.getElementById(imageids.alt).value;
        imagedata.xposition = document.getElementById(imageids.xposition).value;
        imagedata.yposition = document.getElementById(imageids.yposition).value;
        imagedata.targetwidth = document.getElementById(imageids.targetwidth).value;
        imagedata.targetheight = document.getElementById(imageids.targetheight).value;

        // Get the src of the draftfile.
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

        let div = document.getElementById(imageids.coordinates);
        if (imagedata.xposition === "") {
            // If an element was added the coordinates are empty ...
            imagedata.xposition = 0;
        }
        if (imagedata.yposition === "") {
            imagedata.yposition = 0;
        }
        div.innerHTML = (parseInt(number) + 1) + ": " + imagedata.xposition + " / " + imagedata.yposition;
        return imagedata;
    }
};
