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
     *
     * @param {event} event
     */
    async function onChangeAttribute(event) {
        var number = getNumberFromEvent(event);
        if (number >= 0) {
            refreshImage(number);
        // } else {
        //     // TODO: only refresh if titlecolor, titlebackgroundcolor, titlesize was changed.
        //     await refreshAllImages();
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
            showSettingsOfImage(technicalnumber);
            marktargetasselected(event.target);
        }
        // Update coordinates
        let coordinates = document.getElementById('unilabel-imageboard-coordinates');
        let imagedata = getAllImagedataFromForm(technicalnumber);
        coordinates.innerHTML = (parseInt(technicalnumber) + 1) + ": " + imagedata.xposition + " / " + imagedata.yposition;
        console.log('coordinates=', coordinates);
    }

    /**
     *
     * @param {target} target
     */
    function marktargetasselected(target) {
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
        target.classList.add("selected");
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
     * @param {Integer} number of the image
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
            coordinates: "unilabel-imageboard-coordinates",
            // For all images we wil use the same div to show the coorinates
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
        let src = emptyPictureSrc;
        if (imagetag.length && imagetag.length != 0) {
            src = imagetag[0].src;
            src = src.split('?')[0];
        }
        imagedata.src = src;
        imagedata.border = document.getElementById(imageids.border).value;
        imagedata.borderradius = document.getElementById(imageids.borderradius).value;

        let div = document.getElementById(imageids.coordinates);
        if (div) {
            if (imagedata.xposition === "") {
                // If an element was added the coordinates are empty ...
                imagedata.xposition = 0;
            }
            if (imagedata.yposition === "") {
                imagedata.yposition = 0;
            }
            // This will update the coordinates in the html.
            div.innerHTML = (parseInt(number) + 1) + ": " + imagedata.xposition + " / " + imagedata.yposition;
        }
        return imagedata;
    }

    /**
     * This is a helper function to create an html element which can be used to replace another element.
     *
     * @param {String} htmlString
     * @returns {Element}
     */
    function createElementFromHTML(htmlString) {
        var div = document.createElement('div');
        div.innerHTML = htmlString.trim();

        // Change this to div.childNodes to support multiple top-level nodes.
        return div.firstChild;
    }
};
