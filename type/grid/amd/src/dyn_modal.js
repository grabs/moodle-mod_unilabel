/**
 * Modal handler for unilabel type grid
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2025 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Modal from 'unilabeltype_grid/grid_modal';
import log from 'core/log';
import notification from 'core/notification';
import Fragment from 'core/fragment';
import templates from 'core/templates';
import loadingIcon from 'core/loadingicon';


/**
 * Initializes the modal functionality for the unilabel grid type.
 *
 * This function sets up event listeners for grid tiles and manages the creation
 * and display of modals containing tile content. It uses the fragment API to
 * load content dynamically, ensuring each tile's content is loaded only once.
 *
 * @param {number} unilabelid - The id of the unilabel instance.
 */
export const init = async(unilabelid) => {
    // Store loaded modals, keyed by the related tile id.
    const loadedModals = {};
    const spinner = loadingIcon.getIcon();

    // Add a global click event listener to the body.
    // We check later whether or not the event comes from a grid tile.
    document.querySelector('body').addEventListener('click', (event) => {
        const dataset = event.target.dataset;

        // Validate if the clicked element is a unilabeltype_grid tile.
        // This check ensures we only process relevant clicks.
        if (dataset.module !== 'unilabel' || dataset.unilabelid != unilabelid || dataset.type !== 'grid') {
            return;
        }

        // Prevent default link behavior and stop event bubbling.
        event.preventDefault();
        event.stopPropagation();

        // Extract tile-specific data from the clicked element.
        const cmid = dataset.cmid; // The course module id.
        const id = dataset.id; // The tile id.
        const contextid = dataset.contextid; // The Context id of the unilabel instance.

        if (loadedModals[id]) {
            // If the tile related modal is already loaded, display it.
            log.debug('GRID: Displaying existing modal for tile ' + id);
            loadedModals[id].show();
        } else {
            // If the modal isn't loaded, create and show it.
            log.debug('GRID: Creating new modal for tile ' + id);
            createModal(id, cmid, contextid, dataset.title);
        }
    });

    /**
     * Creates and shows a modal for a specific tile.
     *
     * To give the user a little feedback, we first show the modal with a spinner icon.
     * So while the content is loading, which can take a while, the modal is already there.
     *
     * @param {string} id - The id of the tile.
     * @param {string} cmid - The course module id.
     * @param {string} contextid - The context id.
     * @param {string} title - The title the modal is related to.
     * @returns {Promise} A promise that resolves to the modal.
     */
    const createModal = async(id, cmid, contextid, title) => {
        // Initialize the modal with a spinner icon.
        const modal = await Modal.create({
            title: title,
            body: spinner,
            large: true,
            removeOnClose: false,
            show: true,
        });

        // Prepare arguments for content loading.
        const args = {
            type: 'grid',
            id: id,
            cmid: cmid,
            contextid: contextid
        };

        // Load the fragment content and update the modal.
        Fragment.loadFragment('mod_unilabel', 'get_type_content', contextid, args)
            .then(async(html, js) => {
                // Execute any JavaScript returned by the fragment api.
                if (js) {
                    templates.runTemplateJS(js);
                }
                // Update the modal body with the loaded content.
                return modal.setBody(html);
            })
            .catch(notification.exception);

        // Cache the modal for future use.
        loadedModals[id] = modal;
        return modal;
    };
};
