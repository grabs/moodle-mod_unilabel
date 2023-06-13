// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * unilabel helper for activity picker
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    return {
        'init': function(formid) {
            var currentinput;
            $('#unilabel-modal-activity-picker-' + formid).on('show.bs.modal', function() {
                $('#unilabel-modal-activity-picker-' + formid).appendTo('body');
                currentinput = $('#unilabel-modal-activity-picker-' + formid).attr('data-inputid');
            });
            $('.activity-picker-link').on('click', function(event) {
                event.stopPropagation();
                event.preventDefault();
                $('#unilabel-modal-activity-picker-' + formid).modal('hide');
                var url = $(this).attr('href');
                // To make the form aware of the change, we set the data-initial-value to its original value.
                $('#' + currentinput).attr('data-initial-value', $('#' + currentinput).val());
                $('#' + currentinput).val(url); // Set the url into the input field.
                $('#' + currentinput).select(); // Select the the input field.
            });

            $("#search-" + formid).on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#unilabel-activity-picker-list li").filter((index, element) => {
                    // Looking for data-filterstring we can apply the search term.
                    if (element.dataset.filterstring.toLowerCase().indexOf(value) > -1) {
                        $(element).slideDown();
                    } else {
                        $(element).slideUp();
                    }
                    return index;
                });
            });
        }
    };
});
