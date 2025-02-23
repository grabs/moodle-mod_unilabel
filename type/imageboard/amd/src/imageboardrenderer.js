/**
 * Unilabel type imageboard
 *
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Str from 'core/str';

/**
 * @param {string} cmid
 * @param {string} canvaswidth
 * @param {string} canvasheight
 * @param {boolean} autoscale
 * @param {boolean} showgrid
 */
export const init = (cmid, canvaswidth, canvasheight, autoscale, showgrid) => {
    if (autoscale === true) {
        // To autoscale we can not just listen to the resize event from the window.
        // We have to check the size of the module container. It will change its size depending on the left and right drawers.
        // To accomplish this, we use the resizeObserver object.

        // The container we want to be informed about its resize.
        const moduleContainer = document.querySelector("#module-" + cmid + " div.activity-item");

        // Now we create the resizeObserver object.
        const resizeObserver = new ResizeObserver(function() {
            resize(moduleContainer, cmid, canvaswidth, canvasheight);
        });
        resizeObserver.observe(moduleContainer); // Connect the resizeObserver with the moduleContainer.

        resize(moduleContainer, cmid, canvaswidth, canvasheight);
    }

    const gridtoggler = document.getElementById("unilabeltype-imageboard-gridtoggler-" + cmid);
    const togglerText = gridtoggler.querySelector('.unilabeltype-imageboard-toggle-text');
    const helpergrid = document.getElementById("unilabeltype-imageboard-helpergrid-" + cmid);

    if (showgrid === true) {
        showGrid(togglerText, helpergrid);
    }

    gridtoggler.addEventListener("click", function(event) {
        event.stopPropagation();
        event.preventDefault();
        if (helpergrid.classList.contains("hidden")) {
            showGrid(togglerText, helpergrid);
        } else {
            hideGrid(togglerText, helpergrid);
        }
    });

    /**
     * Helper function to remove the grid from imageboard.
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
     * Helper function to get the width of the usable browserarea.
     *
     * @param {Element} moduleContainer
     * @returns {*|number}
     */
    function getWidth(moduleContainer) {
        let style = window.getComputedStyle(moduleContainer);
        let xPadding = parseInt(style.paddingLeft) + parseInt(style.paddingRight);
        return moduleContainer.clientWidth - xPadding;
    }

    /**
     * Resizes the imageboard when autoscale is set true.
     * @param {Element} moduleContainer
     * @param {string} cmid
     * @param {string} canvaswidth
     * @param {string} canvasheight
     */
    function resize(moduleContainer, cmid, canvaswidth, canvasheight) {
        const imageboardContainer = document.getElementById("unilabeltype-imageboard-container-" + cmid);

        let newcanvaswidth = 0;
        newcanvaswidth = getWidth(moduleContainer);
        // Do not make backgroundimage larger than the configured width
        if (newcanvaswidth > canvaswidth) {
            newcanvaswidth = canvaswidth;
        }

        let widthfactor = newcanvaswidth / canvaswidth;

        const mydiv = document.getElementById("unilabeltype-imageboard-" + cmid);

        mydiv.style.transform = "scale(" + widthfactor + ")";
        mydiv.style.transformOrigin = "0 0";

        // Make the imageboardContainer just 25px larger than the imageboard ... padding-left: 25px moves the container
        // to the right so that 25px are cut off so wie add + 25px.
        imageboardContainer.style.width = mydiv.offsetWidth * widthfactor + 25 + "px";

        // The height of the white space that is generated by scaling the div can be calculated
        let heightOfSpace = canvasheight * (1 - widthfactor);
        mydiv.style.marginBottom = "-" + heightOfSpace + "px";
    }
};

