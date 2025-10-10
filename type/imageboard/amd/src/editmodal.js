/**
 * Unilabel type imageboard
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import modalHelper from 'mod_unilabel/modal_helper';
// import $ from 'jquery'; // Still needed for actions on bootstrap 4 modal dialogs.
import * as Str from 'core/str';
import Modal from 'theme_boost/bootstrap/modal';

const fixModalPosition = (modalselector) => {
    var modal = document.querySelector(modalselector);
    var form = modal.closest('form');

    document.querySelector(modalselector).addEventListener('show.bs.modal', function() {
        form.append(modal);
    });

    // Hack for stacked modals to show the backdrop with the right z-index.
    document.addEventListener('show.bs.modal', function(event) {
        if (event.target.classList.contains('modal')) {
            var visibleModals = document.querySelectorAll('.modal.show').length;
            var zIndex = 1040 + (10 * visibleModals);
            event.target.style.zIndex = zIndex;
            setTimeout(function() {
                var backdrops = document.querySelectorAll('.modal-backdrop:not(.modal-stack)');
                backdrops.forEach(function(backdrop) {
                    backdrop.style.zIndex = zIndex - 1;
                    backdrop.classList.add('modal-stack');
                });
            }, 100);
        }
    });

    modal.addEventListener('shown.bs.modal', function(event) {
        event.target.querySelector('.modal-dialog').focus();
    });

    // Hack to enable stacked modals by making sure the .modal-open class
    // is set to the <body> when there is at least one modal open left.
    document.addEventListener('hidden.bs.modal', function() {
        if (document.querySelectorAll('.modal.show').length > 0) {
            document.body.classList.add('modal-open');
        }
    });
};

export const init = () => {
    const modalId = 'unilabeltype_imageboard_modal'; // Define the modal prefix.
    const formHeaderPrefix = 'id_singleelementheader_';
    var hidenumber = 0;
    var singelelementheader;
    var counter = 0;

    var modalheader = document.querySelector('#' + modalId + ' .modal-header .modal-title');

    // Hide all elements with an id starting with "formHeaderPrefix"
    while (counter < 10000) { // There shouldn't be more than 10000 elements.
        singelelementheader = document.querySelector('#' + formHeaderPrefix + hidenumber);
        if (!singelelementheader) {
            break;
        }
        singelelementheader.classList.add('d-none'); // Hide the element by adding 'd-none' class.
        hidenumber++;
        counter++;
    }

    var actualnumber = -1; // Initialize the actual number to -1.
    var currentparent = null; // Variable to store the current parent element.
    fixModalPosition('#' + modalId); // Initialize the modal helper.
    document.querySelector('#imageboardcontainer').addEventListener('click', function(e) {
        var myModal;
        if (e.target.dataset.type === 'imageaction') {
            e.stopPropagation();
            e.preventDefault();
            actualnumber = parseInt(e.target.dataset.number); // Set the actual number from the clicked element.
            myModal = new Modal(document.querySelector("#unilabeltype_imageboard_modal"));
            myModal.show();
        }
        if (e.target.dataset.type === 'deleteimage') {
            e.stopPropagation();
            e.preventDefault();
            var deletenumber = parseInt(e.target.dataset.number); // Get the number of the image to delete.
            myModal = new Modal(document.querySelector('#unilabel_imageboard_confirm_inline_' + deletenumber));
            myModal.show();
        }

    });

    document.querySelector('#' + modalId).addEventListener('show.bs.modal', function() {
        var src = document.querySelector('#' + formHeaderPrefix + actualnumber + ' .element-edit-container');
        currentparent = src.parentElement; // Store the current parent element.
        var dst = document.querySelector('#' + modalId + ' .modal-body');
        dst.append(src); // Move the edit container to the modal body.

        Str.get_string('imagenr', 'unilabeltype_imageboard', actualnumber + 1).done(function(text) {
            modalheader.innerText = text;
        });
    });

    document.querySelector('#' + modalId).addEventListener('hidden.bs.modal', function(event) {
        event.target.querySelector('.modal-dialog').classList.remove('focus');

        var src = document.querySelector('#' + modalId + ' .modal-body .element-edit-container');
        var dst = currentparent;
        dst.append(src); // Move the edit container back to its original parent when the modal is hidden.
    });

    var mform = document.querySelector('[id^="mform"]');
    mform.addEventListener('itemadded', function(e) {
        actualnumber = e.detail; // Set the actual number from the event detail.
        var myModal = new Modal(document.querySelector('#' + modalId));
        myModal.show();
    });

    // Prevent submit if "return" key is pressed on image input element.
    mform.addEventListener('submit', function(e) {
        // Check if the active element (the element that triggered the submit)
        // is inside the ".element-edit-container".
        var editContainer = document.activeElement.closest('.element-edit-container');
        if (editContainer) {
            e.preventDefault();
            return false;
        }
        // If not, let the form submit normally.
        return true;
    });

    var draggablemodal = document.querySelector('#' + modalId + '.draggable'); // Get the draggable modal element.
    var draggableheader = draggablemodal.querySelector('.modal-header'); // Get the modal header for drag functionality.

    /**
     * Handles the start of a drag operation on the modal.
     * This function sets up event listeners for both mouse and touch events to enable dragging.
     *
     * @param {MouseEvent|TouchEvent} e - The event object for the mousedown or touchstart event.
     */
    function handleStart(e) {
        e.preventDefault();
        var touch = e.touches ? e.touches[0] : e;
        var offsetX = touch.clientX - draggablemodal.getBoundingClientRect().left;
        var offsetY = touch.clientY - draggablemodal.getBoundingClientRect().top;

        /**
         * Handles the movement of the modal during a drag operation.
         * Updates the modal's position based on the current mouse or touch position.
         *
         * @param {MouseEvent|TouchEvent} e - The event object for the mousemove or touchmove event.
         */
        function moveHandler(e) {
            e.preventDefault();
            var moveTouch = e.touches ? e.touches[0] : e;
            draggablemodal.style.left = (moveTouch.clientX - offsetX) + 'px';
            draggablemodal.style.top = (moveTouch.clientY - offsetY) + 'px';
        }

        /**
         * Handles the end of a drag operation.
         * Removes all event listeners that were added for the drag operation.
         */
        function endHandler() {
            document.removeEventListener('mousemove', moveHandler);
            document.removeEventListener('mouseup', endHandler);
            document.removeEventListener('touchmove', moveHandler);
            document.removeEventListener('touchend', endHandler);
        }

        // Add all event listeners for the drag operations.
        document.addEventListener('mousemove', moveHandler);
        document.addEventListener('mouseup', endHandler);
        document.addEventListener('touchmove', moveHandler);
        document.addEventListener('touchend', endHandler);
    }

    // Add event listener for start dragging the modal.
    draggableheader.addEventListener('mousedown', handleStart);
    draggableheader.addEventListener('touchstart', handleStart);
};
