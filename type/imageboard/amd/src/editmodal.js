/**
 * Unilabel type imageboard
 *
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import modalHelper from 'mod_unilabel/modal_helper';
import $ from 'jquery'; // Still needed for actions on bootstrap 4 modal dialogs.

const fixModalPosition = (modalselector) => {
    var modal = document.querySelector(modalselector);
    var form = modal.closest('form');

    $(modalselector).on('show.bs.modal', function() {
        form.append(modal);
    });

    // Hack for stacked modals to show the backdrop with the right z-index.
    $(document).on('show.bs.modal', '.modal', function() {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 100);
    });

    // Hack to enable stacked modals by making sure the .modal-open class
    // is set to the <body> when there is at least one modal open left.
    $(document).on('hidden.bs.modal', function() {
        if ($('.modal.show').length > 0) {
            $('body').addClass('modal-open');
        }
    });
};

export const init = () => {
    const modalId = 'unilabeltype_imageboard_modal'; // Define the modal prefix.
    const formHeaderPrefix = 'id_singleelementheader_';
    var hidenumber = 0;
    var singelelementheader;
    var counter = 0;

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
        if (e.target.dataset.type === 'imageaction') {
            actualnumber = parseInt(e.target.dataset.number); // Set the actual number from the clicked element.
            $('#' + modalId).modal(); // Show the modal.
        }
        if (e.target.dataset.type === 'deleteimage') {
            e.stopPropagation();
            e.preventDefault();
            var deletenumber = parseInt(e.target.dataset.number); // Get the number of the image to delete.
            $('#unilabel_imageboard_confirm_inline_' + deletenumber).modal(); // Show the confirmation modal.
        }

    });
    $('#' + modalId).on('show.bs.modal', function() {
        var src = document.querySelector('#' + formHeaderPrefix + actualnumber + ' .element-edit-container');
        currentparent = src.parentElement; // Store the current parent element.
        var dst = document.querySelector('#' + modalId + ' .modal-body');
        dst.append(src); // Move the edit container to the modal body.
    });
    $('#' + modalId).on('hidden.bs.modal', function() {
        var src = document.querySelector('#' + modalId + ' .modal-body .element-edit-container');
        var dst = currentparent;
        dst.append(src); // Move the edit container back to its original parent when the modal is hidden.
    });

    var mform = document.querySelector('[id^="mform"]');
    mform.addEventListener('itemadded', function(e) {
        actualnumber = e.detail; // Set the actual number from the event detail.
        $('#' + modalId).modal(); // Show the modal when a new item is added.
    });

    var modal = document.querySelector('#' + modalId + '.draggable'); // Get the draggable modal element.
    var header = modal.querySelector('.modal-header'); // Get the modal header for drag functionality.

    /**
     * Handles the start of a drag operation on the modal.
     * This function sets up event listeners for both mouse and touch events to enable dragging.
     *
     * @param {MouseEvent|TouchEvent} e - The event object for the mousedown or touchstart event.
     */
    function handleStart(e) {
        e.preventDefault();
        var touch = e.touches ? e.touches[0] : e;
        var offsetX = touch.clientX - modal.getBoundingClientRect().left;
        var offsetY = touch.clientY - modal.getBoundingClientRect().top;

        /**
         * Handles the movement of the modal during a drag operation.
         * Updates the modal's position based on the current mouse or touch position.
         *
         * @param {MouseEvent|TouchEvent} e - The event object for the mousemove or touchmove event.
         */
        function moveHandler(e) {
            e.preventDefault();
            var moveTouch = e.touches ? e.touches[0] : e;
            modal.style.left = (moveTouch.clientX - offsetX) + 'px';
            modal.style.top = (moveTouch.clientY - offsetY) + 'px';
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
    header.addEventListener('mousedown', handleStart);
    header.addEventListener('touchstart', handleStart);

};
