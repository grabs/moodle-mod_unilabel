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

/**
 * Initializes the modal functionality for the unilabel grid type.
 *
 * It loads the content of a grid tile by using the fragment api.
 * Each tile is loade only once.
 * @param {number} unilabelid - The ID of the unilabel instance.
 */
export const init = (unilabelid) => {
    // Object to store loaded modals, keyed by the tile id the modal is related to.
    const loadedModals = {};

    // Add a click event listener to the body element.
    // We check later whether or not the event comes from a grid tile.
    document.querySelector('body').addEventListener('click', (event) => {
        const dataset = event.target.dataset; // Get all dataset attributes from the event.

        // Check whether the clicked element is a link from a unilabeltype_grid tile.
        if (dataset.module != 'unilabel' || dataset.unilabelid != unilabelid || dataset.type != 'grid') {
            return;
        }

        // Prevent default action and stop event propagation.
        event.preventDefault();
        event.stopPropagation();

        // Extract necessary data from the clicked tile.
        const cmid = event.target.dataset.cmid; // The course module id.
        const id = event.target.dataset.id; // The tile id.
        const contextid = event.target.dataset.contextid; // The contextid.

        // Check whether the tile has a loaded modal.
        if (loadedModals[id]) {
            // If the modal is already loaded, show it.
            log.debug('GRID: Show modal-' + id);
            loadedModals[id].show();
        } else {
            // If the modal is not loaded, fetch its content and create it.
            log.debug('GRID: Load fragment and create modal');
            const args = {
                type: 'grid',
                id: id,
                cmid: cmid,
                contextid: contextid
            };
            // Load the modal content using the Fragment API.
            Fragment.loadFragment('mod_unilabel', 'get_type_content', contextid, args).then(async(html, js) => {
                // We use the html and the title from clicked elements dataset to create the modal.
                const modal = await createModal(id, html, dataset.title);
                if (js) {
                    templates.runTemplateJS(js);
                }
                return modal;
            }).catch(notification.exception);
        }
    });

    /**
     * Creates a modal with the given content and title.
     * @param {string} id - The tile id to identify the modal later.
     * @param {string} content - The HTML content of the modal body.
     * @param {string} title - The title of the modal.
     */
    const createModal = (id, content, title) => {
        log.debug('GRID: Create modal width content');
        log.debug(content);

        // Create the modal using the Modal API.
        Modal.create({
            title: title,
            body: content,
            large: true,
            removeOnClose: false,
            show: true,
        }).then((modal) => {
            // Store the created modal in the loadedModals object.
            loadedModals[id] = modal;
            return true;
        }).catch(notification.exception);
    };
};
