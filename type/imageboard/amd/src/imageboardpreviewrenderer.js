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
import cfg from 'core/config';

/**
 * Initialize the imageboard renderer.
 *
 * @param {Integer} canvaswidth
 * @param {Integer} canvasheight
 * @param {String} gridcolor
 * @param {Integer} xsteps
 * @param {Integer} ysteps
 */
export const init = async(canvaswidth, canvasheight, gridcolor, xsteps, ysteps) => {
    canvaswidth = parseInt(canvaswidth, 10);
    canvasheight = parseInt(canvasheight, 10);
    xsteps = parseInt(xsteps, 10);
    ysteps = parseInt(ysteps, 10);

    let emptyPictureSrc = cfg.wwwroot + '/mod/unilabel/type/imageboard/pix/empty-picture.gif';

    let imageList = new Array();
    let lastImageNumber = -1;

    // The next calls depends on each other, so we wait for each of them to be ready.
    await registerAllEventlistener();
    await refreshBackgroundImage();
    await refreshAllImages();
    await renderHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);

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

    const imageboarddraganddrop = await import('unilabeltype_imageboard/imageboarddraganddrop');
    imageboarddraganddrop.init();

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
     * This function handles all focus out events if the event is from on of our input fields.
     * @param {event} event
     */
    async function onChangeAttribute(event) {
        let technicalnumber = -1;
        // 1. Check where the focus out event was created form input or imagesetting input.
        const eventid = event.target.getAttribute('id');
        let eventsourceimagesetting = eventid.split("id-unilabeltype-imageboard-imagesettings-dialog-")[1];
        let eventsourceform = eventid.split("id_unilabeltype_imageboard_")[1];
        // ToDo:  Check if it is a focus out event has to be more precise ... Delete-Icon!!!!
        if (typeof eventsourceimagesetting !== "undefined" || typeof eventsourceform !== "undefined") {

            // 2. If ID starts with id_unilabeltype_imageboard_ then focus out came from form input fields.
            if (typeof eventsourceimagesetting !== "undefined" && eventsourceimagesetting !== '') {
                // Call updateForm and use as parameter the input field that should be updated in the form.
                technicalnumber = updateForm(eventsourceimagesetting);
            }

            // 3. ID starts with id_unilabeltype_imageboard_. Focus from the form The imagesettings must be updated.
            if (typeof eventsourceform !== "undefined" && eventsourceform !== '') {
                // We have to update all field in imagesettingsdialog
                // Aus dem event nun doch die nummer auslesen
                technicalnumber = eventsourceform.substr(eventsourceform.length - 1, eventsourceform.length);
                writeFormdataOfImageToImagesettingsdialogupdate(technicalnumber);
            }

            // Now we know which image was changed and we can refresh on or all images.
            if (technicalnumber >= 0) {
                refreshImage(technicalnumber);
            // } else {
            //     // TODO: only refresh if titlecolor, titlebackgroundcolor, titlesize was changed.
            //     await refreshAllImages();
            }
        }
    }

    /**
     *
     * @param {event} event
     */
    function onRightclick(event) {
        event.preventDefault();
        // Get the number of the image that was selected with the right mouse button
        var idoftarget = event.target.getAttribute('id');
        if (!idoftarget) {
            return;
        }

        // Check, if idoftarget ist an id of an image
        let technicalnumber = idoftarget.split('unilabel-imageboard-imageid-')[1];
        // Oder ein Titel wurde angeklickt
        if (!technicalnumber) {
            technicalnumber = idoftarget.split('id_elementtitle-')[1];
        }
        if (technicalnumber) {
            // Update the imagesettingsdialog with the data of that image and show the dialog
            writeFormdataOfImageToImagesettingsdialogupdate(technicalnumber);
            // Wenn das selectierte Bild eine andere nummer hat als das aktuelle imagesettings anzeigt dann auf jeden fall anzeigen
            const imagenumber =
                parseInt(document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-number').innerHTML);
            if (technicalnumber == imagenumber) {
                imagesettingsdivvisibilitytoggler();
            } else {
                // Imagesettingsdialog has do be visible. So if it is already visible it is no problem to set it
                // once again visible. If it is hidden, the set it to visible.
                imagesettingsdivvisibility('visible');
            }
        } else {
            // No image was selected ... do nothing.
        }
    }

    /**
     *
     */
    function imagesettingsdivvisibilitytoggler() {
        let imagesettingsdiv = document.getElementById("id-unilabeltype-imageboard-imagesettings-dialog");
        if (imagesettingsdiv && imagesettingsdiv.style && imagesettingsdiv.style.visibility == 'visible') {
            imagesettingsdiv.style.visibility = 'hidden';
        } else {
            if (imagesettingsdiv && imagesettingsdiv.style && imagesettingsdiv.style.visibility == 'hidden') {
                imagesettingsdiv.style.visibility = 'visible';
            }
        }
    }

    /**
     * Upates the input field in the mform
     *
     * @param {string} eventsourceimagesetting
     * @returns {number}
     */
    function updateForm(eventsourceimagesetting) {
        const technicalnumber =
            parseInt(document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-number').innerHTML) - 1;
        // Only do something if the changed value is an integer.
        // ToDo: Also add check all other input fields.
        let value =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-' + eventsourceimagesetting).value;
        if (eventsourceimagesetting === 'xposition' ||
            eventsourceimagesetting === 'yposition' ||
            eventsourceimagesetting === 'border' ||
            eventsourceimagesetting === 'borderradius') {
            let num = Number(value);
            if (value !== '' && !Number.isInteger(num)) {
                return -1;
            }
        }

        let field = document.getElementById('id_unilabeltype_imageboard_' + eventsourceimagesetting + '_' + technicalnumber);
        if (field !== null) {
            field.value = value;
        }
        return technicalnumber;
    }

    /**
     *
     * @param {number} technicalnumber
     */
    function writeFormdataOfImageToImagesettingsdialogupdate(technicalnumber) {
        let selectedImage = getAllImagedataFromForm(technicalnumber);
        // Den Imagesettings-Anzeigebereich aktualisieren
        const imagesettingsNumber = document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-number');
        imagesettingsNumber.innerHTML = (parseInt(selectedImage.technicalnumber) + 1);

        const imagesettingsTitle = document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-title');
        imagesettingsTitle.value = selectedImage.title;

        const imagesettingsInputPositionX =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-xposition');
        imagesettingsInputPositionX.value = parseInt(selectedImage.xposition);
        const imagesettingsInputPositionY =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-yposition');
        imagesettingsInputPositionY.value = parseInt(selectedImage.yposition);

        const imagesettingsInputTargetwidth =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-targetwidth');
        imagesettingsInputTargetwidth.value = parseInt(selectedImage.targetwidth);
        const imagesettingsInputTargetheight =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-targetheight');
        imagesettingsInputTargetheight.value = parseInt(selectedImage.targetheight);

        const imagesettingsInputBorder = document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-border');
        imagesettingsInputBorder.value = parseInt(selectedImage.border);

        const imagesettingsInputBorderradius =
            document.getElementById('id-unilabeltype-imageboard-imagesettings-dialog-borderradius');
        imagesettingsInputBorderradius.value = parseInt(selectedImage.borderradius);
    }


    /**
     * Add a new preview image when a new element is added.
     */
    function onAddElement() {
        addImageToDom(parseInt(lastImageNumber) + 1);
    }

    /**
     * Remove the preview image related to the element which was removed.
     * The index of the element comes from the detail property of the event object.
     * @param {*} event
     */
    function onRemoveElement(event) {
        let removedImage = document.querySelector('#unilabel-imageboard-element-' + event.detail);
        if (removedImage) {
            removedImage.remove();
        }
    }

    /**
     * Register eventlistener to the all input fields of the form to register
     * focus-out events from input fields in order to trigger a fresh of the preview.
     */
    async function registerAllEventlistener() {
        var mform = document.querySelectorAll('[id^="mform"]')[0];
        // We register one listener per eventtype to the mform and use the bubble-event-feature to check out
        // the target of an event.

        // All change events will be handeled by the onChangeAttribute function.
        mform.addEventListener("change", async(event) => {
            await onChangeAttribute(event);
        });

        // All keyup events will be handeled by the onChangeAttribute function.
        mform.addEventListener("keyup", async(event) => {
            await onChangeAttribute(event);
        });

        // If there is a new element added, the event "itemadded" is fired and we can add a new preview image.
        mform.addEventListener("itemadded", (event) => {
            onAddElement(event);
        });

        // If there is an element removed, the event "itemremoved" is fired and we can remove the related preview image.
        mform.addEventListener("itemremoved", (event) => {
            onRemoveElement(event);
        });

        // All click-events will be handeled by oneListenerForAllInputClick.
        mform.addEventListener("click", onclickExecute, false);

        // All click-events will be handeled by oneListenerForAllInputClick.
        mform.addEventListener("contextmenu", onRightclick, false);

        // First: When uploading a backgroundimage the backgroundimage of the backgroundimagediv must be updated.
        // TODO: better use eventlistener
        let backgroundfileNode = document.getElementById('id_unilabeltype_imageboard_backgroundimage_fieldset');
        require(['core_form/events'], function(FormEvent) {
            backgroundfileNode.addEventListener(FormEvent.eventTypes.uploadChanged, (event) => {
                // In the event object the target is the filemanager we want to access.
                const filemanager = event.target;
                const interval = setInterval(async() => {
                    // As long the filemanager is updating, e.g. while uploading large images, we have to wait.
                    if (!filemanager.classList.contains('fm-updating')) {
                        clearInterval(interval);
                        // If the filemanager does not have any items we can reset our preview image.
                        if (filemanager.classList.contains('fm-noitems')) {
                            // The filemanager caches the last loaded image, so we would not realy know
                            // if it is deleted. To make sure we know later too, we remove that cached image.
                            let img = event.target.getElementsByTagName('img')[0];
                            img.remove();
                            // Now reset the preview image.
                            refreshBackgroundImage();
                        }
                    }
                }, 100);
            });
        });

        if (backgroundfileNode) {
            let observer = new MutationObserver(refreshBackgroundImage);
            observer.observe(backgroundfileNode, {attributes: true, childList: true, subtree: true});
        }
        // Also add listener for canvas size
        let canvasx = document.getElementById('id_unilabeltype_imageboard_canvaswidth');
        if (canvasx) {
            canvasx.addEventListener('change', refreshBackgroundImage);
        }
        let canvasy = document.getElementById('id_unilabeltype_imageboard_canvasheight');
        if (canvasy) {
            canvasy.addEventListener('change', refreshBackgroundImage);
        }
    }


    /**
     * OnClickEvent is needed for the close-Button of imagesettings.
     * @param {event} event
     */
    function onclickExecute(event) {
        var targetid = event.target.getAttribute('id');
        var mform = targetid.split('button-mform1')[1];
        if (mform) {
            // Das wird über den event itemadded bereits abgearbeitet ...
        } else {
            // Wenn kein Element hinzugefügt wird prüfen, ob man den Imagesettingsdialog ausblenden will.
            var imagesettindgdialogid = event.target.getAttribute('id');
            if (imagesettindgdialogid === 'id-unilabeltype-imageboard-imagesettings-dialog-close') {
                imagesettingsdivvisibility('hidden');
            }
        }
    }
    /**
     *
     * @param {string} visibility
     */
    function imagesettingsdivvisibility(visibility) {
        let imagesettingsdiv = document.getElementById("id-unilabeltype-imageboard-imagesettings-dialog");
        imagesettingsdiv.style.visibility = visibility;
    }

    /**
     * Sets the background image of the SVG to the current image in filemanager.
     */
    async function refreshBackgroundImage() {
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
            backgrounddiv.style.background = 'red'; // TODO: Do wie need this code? Just to indicate changes during dev.
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
            await refreshHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);
        } else {
            // Image might be deleted so update the backroundidv and remove backgroundimage in preview;
            // TODO: If (previewimage.length > 0) does not recognize when an image is deleted so we need a different condition!
            backgrounddiv.style.background = 'green'; // TODO: check if this is needed. just to indicate changes during development.
            backgrounddiv.style.backgroundImage = "url('')";
            const canvaswidthinput = document.getElementById('id_unilabeltype_imageboard_canvaswidth');
            let canvaswidthselected = canvaswidthinput.selectedOptions;
            let canvaswidth = canvaswidthselected[0].value;
            backgrounddiv.style.width = canvaswidth + "px";

            const canvasheightinput = document.getElementById('id_unilabeltype_imageboard_canvasheight');
            let canvasheightselected = canvasheightinput.selectedOptions;
            let canvasheight = canvasheightselected[0].value;
            backgrounddiv.style.height = canvasheight + "px";
            await refreshHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);
        }
        log.debug('canvas size changed');
        const myevent = new CustomEvent('canvaschanged');
        document.dispatchEvent(myevent);
    }

    /**
     * Create the helper grid as async function so we can wait till it is ready.
     *
     * @param {number} canvaswidth
     * @param {number} canvasheight
     * @param {string} gridcolor
     * @param {number} xsteps
     * @param {number} ysteps
     */
    async function renderHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps) {
        let gridcontent = await getHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);
        // We have to get the actual content, combine it with the rendered image and replace then the actual content.
        let imageboardcontainer = document.getElementById('imageboardcontainer').innerHTML;
        let combined = "<div>" + imageboardcontainer + "</div>" + gridcontent.resultHtml;
        Templates.replaceNodeContents('#imageboardcontainer', combined, gridcontent.resultJs);
        // TODO: Check.
        return;
    }

    /**
     * Refresh the helper grid as async function so we can wait till it is ready.
     *
     * @param {number} canvaswidth
     * @param {number} canvasheight
     * @param {string} gridcolor
     * @param {number} xsteps
     * @param {number} ysteps
     */
    async function refreshHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps) {
        let gridContainer = document.querySelector('#unilabeltype-imageboard-helpergrid-0');
        if (gridContainer) {
            let gridcontent = await getHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps);
            let newGrid = createElementFromHTML(gridcontent.resultHtml);
            gridContainer.replaceWith(newGrid);
            Templates.runTemplateJS(gridcontent.resultJs);
        } else {
            log.debug('No grid found :(');
        }
    }

    /**
     * Create the helpergrid and returns the created html.
     * @param {*} canvaswidth
     * @param {*} canvasheight
     * @param {*} gridcolor
     * @param {*} xsteps
     * @param {*} ysteps
     * @returns {*} An object with the properties "resultHtml" and "resultJs".
     */
    async function getHelpergrid(canvaswidth, canvasheight, gridcolor, xsteps, ysteps) {
        let helpergrids = [];
        for (let y = 0; y < canvasheight; y += ysteps) {
            for (let x = 0; x < canvaswidth; x += xsteps) {
                let helpergrid = {};
                helpergrid.x = x;
                helpergrid.y = y;
                if (x + xsteps > canvaswidth) {
                    helpergrid.xsteps = (x + xsteps) - canvaswidth;
                }
                if (y + ysteps > canvasheight) {
                    helpergrid.ysteps = (y + ysteps) - canvasheight;
                }
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
        var resultHtml = '';
        var resultJs = '';
        await Templates.renderForPromise('unilabeltype_imageboard/imageboard_helpergridpreview', context).then(({html, js}) => {
            // We have to get the actual content, combine it with the rendered image and replace then the actual content.
            resultHtml = html;
            resultJs = js;
            return;
        }).catch(() => {
            log.debug('Rendering failed');
        });

        return {
            resultHtml: resultHtml,
            resultJs: resultJs
        };
    }


    /**
     * Gets the number of ALL elements in the form and then adds a div for each element to the dom if not already exists.
     * This function is designed as async function, so we can wait till all depending actions are ready.
     */
    async function refreshAllImages() {
        const singleElements = document.querySelectorAll('[id^="fitem_id_unilabeltype_imageboard_image_"]');
        for (let i = 0; i < singleElements.length; i++) {
            // TODO: Skip removed elements that are still in the dom but hidden.
            let singleElement = singleElements[i].getAttribute('id');
            let number = singleElement.split('fitem_id_unilabeltype_imageboard_image_')[1];
            // Check if there exists already the elment for the image with number xyz.
            if (!document.getElementById('unilabel-imageboard-element-' + number)) {
                // Add the new image and wait till it is rendered.
                await addImageToDom(number);
                // Refresh the presentation of the image.
                refreshImage(number);
            } else {
                // Refresh the presentation of the image.
                refreshImage(number);
            }
        }
    }

    /**
     *
     * @param {Integer} number
     */
    async function addImageToDom(number) {
        // If there is an invalid number, we do nothing.
        if (number < 0) {
            return;
        }

        // Check whether the image has to be created.
        if (!document.getElementById('unilabel-imageboard-element-' + number)) {
            // Get the rendered html for the new preview image.
            let result = await renderAddedImage(number);
            if (result.resultHtml) {
                // Place the new rendered image at the end of the div all preview images are in.
                document.querySelector('#imageboardcontainer div').insertAdjacentHTML('beforeend', result.resultHtml);
                // Run the JS for the new image. This actually does nothing but sometime it will.
                Templates.runTemplateJS(result.resultJs);


                // Add listeners to the filemanager.
                let imagefileNode = document.getElementById('fitem_id_unilabeltype_imageboard_image_' + (number));
                if (imagefileNode) {
                    // Add the listener to watch changes in the filemanager.
                    // We use this to be aware of deleted images.
                    // From core_form/events come the event types. We use the event type "uploadChanged".
                    require(['core_form/events'], function(FormEvent) {
                        imagefileNode.addEventListener(FormEvent.eventTypes.uploadChanged, (event) => {
                            // In the event object the target is the filemanager we want to access.
                            const filemanager = event.target;
                            const interval = setInterval(async() => {
                                // As long the filemanager is updating, e.g. while uploading large images, we have to wait.
                                if (!filemanager.classList.contains('fm-updating')) {
                                    clearInterval(interval);
                                    // If the filemanager does not have any items we can reset our preview image.
                                    if (filemanager.classList.contains('fm-noitems')) {
                                        // The filemanager caches the last loaded image, so we would not realy know
                                        // if it is deleted. To make sure we know later too, we remove that cached image.
                                        let img = event.target.getElementsByTagName('img')[0];
                                        img.remove();
                                        // Now reset the preview image.
                                        resetPreviewImage(number);
                                    }
                                }
                            }, 100);
                        });
                    });

                    // Add the mutation listener for the filepicker element related to the new image.
                    let observer = new MutationObserver(async() => {
                        await addImageToDom(number);
                        // Refresh the presentation of the image.
                        refreshImage(number);
                    });
                    observer.observe(imagefileNode, {attributes: true, childList: true, subtree: true});
                }
            }
        } else {
            // Div already exists so we need only to refresh the image because we only uploaded a new image
            // to an already existing div.
            refreshImage(number);
        }
    }

    /**
     * Reset the preview image if the image is deleted but the element is still there.
     * @param {Integer} number
     */
    function resetPreviewImage(number) {
        let prevImg = document.querySelector('#unilabel-imageboard-imageid-' + number);
        prevImg.setAttribute('src', emptyPictureSrc);
        prevImg.style.background = 'none';
        prevImg.style.height = 'auto';
        prevImg.style.width = 'auto';
        prevImg.style.border = 'none';
        prevImg.style.borderRadius = 0;
        log.debug('Image nr ' + number + ' removed');
        document.getElementById('id_unilabeltype_imageboard_targetwidth_' + number).value = 0;
        document.getElementById('id_unilabeltype_imageboard_targetheight_' + number).value = 0;
        document.getElementById('id_unilabeltype_imageboard_border_' + number).value = 0;
        document.getElementById('id_unilabeltype_imageboard_borderradius_' + number).value = 0;
    }

    /**
     *
     * @param {number} number of
     */
    async function renderAddedImage(number) {
        if (imageList.includes(number)) {
            return {};
        }
        lastImageNumber = number;
        imageList.push(lastImageNumber);
        const context = {
            // Data to be rendered
            number: number,
            displaynumber: parseInt(number) + 1,
            title: "title",
            src: emptyPictureSrc
        };

        let resultHtml;
        let resultJs;
        await Templates.renderForPromise('unilabeltype_imageboard/previewimage', context).then(({html, js}) => {
            // We have to get the actual content, combine it with the rendered image and replace then the actual content.
            resultHtml = html;
            resultJs = js;
            return;
        }).catch(() => {
            log.debug('Error while rendering from template');
        });

        return {
            resultHtml: resultHtml,
            resultJs: resultJs
        };
    }

    /**
     * If an image was uploaded or inputfields in the form changed then we need to refresh
     * this image.
     * @param {Integer} number
     */
    async function refreshImage(number) {
        // Only refresh a real image with a positive number.
        if (number >= 0) {
            let imageid = document.getElementById("unilabel-imageboard-imageid-" + number);
            if (imageid) {
                // Fill all the needed values for imagedata.
                let imagedata = getAllImagedataFromForm(number);
                // A imageid.style.background = imagedata.titlebackgroundcolor;
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
            }
        }
    }

    /**
     * Get all data from image that is stored in the form and collects them in one array.
     *
     * @param {Integer} technicalnumber of the image
     * @returns {*[]} Array with the collected information that are set in the form for the image.
     */
    function getAllImagedataFromForm(technicalnumber) {
        let imageids = {
            title: 'id_unilabeltype_imageboard_title_' + technicalnumber,
            titlecolor: 'id_unilabeltype_imageboard_titlecolor_colourpicker',
            titlebackgroundcolor: 'id_unilabeltype_imageboard_titlebackgroundcolor_colourpicker',
            titlelineheight: 'id_unilabeltype_imageboard_titlelineheight',
            fontsize: 'id_unilabeltype_imageboard_fontsize',
            alt: 'id_unilabeltype_imageboard_alt_' + technicalnumber,
            xposition: 'id_unilabeltype_imageboard_xposition_' + technicalnumber,
            yposition: 'id_unilabeltype_imageboard_yposition_' + technicalnumber,
            targetwidth: 'id_unilabeltype_imageboard_targetwidth_' + technicalnumber,
            targetheight: 'id_unilabeltype_imageboard_targetheight_' + technicalnumber,
            src: '',
            border: 'id_unilabeltype_imageboard_border_' + technicalnumber,
            borderradius: 'id_unilabeltype_imageboard_borderradius_' + technicalnumber,
        };

        let imagedata = {};
        imagedata.technicalnumber = technicalnumber;
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
        const element = document.getElementById('id_unilabeltype_imageboard_image_' + technicalnumber + '_fieldset');
        const imagetag = element.getElementsByTagName('img');
        let src = emptyPictureSrc;
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
     * This is a helper function to create an html element which can be used to replace another element.
     *
     * @param {Sring} htmlString
     * @returns {Element}
     */
    function createElementFromHTML(htmlString) {
        var div = document.createElement('div');
        div.innerHTML = htmlString.trim();

        // Change this to div.childNodes to support multiple top-level nodes.
        return div.firstChild;
    }
};
