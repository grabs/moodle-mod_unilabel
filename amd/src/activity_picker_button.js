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
 * unilabel helper for activity picker button
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    return {
        'init': function(formid, inputidbase) {
            $('[data-button="' + formid + '"]').on('click', function(event) {
                event.preventDefault();
                // Get the id (repeat number) from parent element.
                var id = $(this).parent().parent().parent().attr('id').split("_").slice(-1);
                var currentinput = inputidbase + id;

                $('#unilabel-modal-activity-picker-' + formid).attr('data-inputid', currentinput);
                $('#unilabel-modal-activity-picker-' + formid).modal('show');
            });
        }
    };
});
