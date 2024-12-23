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

import $ from 'jquery';
import log from 'core/log';

/**
 * Set the url input field when loading the mform. It modifies only already saved elements and not the new one.
 * @param {*} formid
 * @param {*} inputidbase
 * @param {*} labelidbase
 */
export const init = async(formid, inputidbase, labelidbase) => {
    const str = await import('core/str');
    const deletestr = await str.get_string('delete');
    // The inputswitcher switches an text input element into a hidden element and added an activitylink clone.
    const inputswitcher = await import('mod_unilabel/activity_picker_input_switcher');

    let pickerlist = document.querySelector('#unilabel-activity-picker-list');
    let links = pickerlist.querySelectorAll('a.activity-picker-link');

    let maybeactivityelement;

    log.debug('Get all picker-button-links');
    let pickerbuttons = document.querySelectorAll('a.unilabel-picker-button-link');
    log.debug(pickerbuttons);
    pickerbuttons.forEach(el => {
        try {
            let id = el.parentElement.parentElement.parentElement.id.split("_").slice(-1);
            if (id) {
                let currentinput = document.querySelector('#' + inputidbase + id);
                log.debug(currentinput.value);
                links.forEach(link => {
                    if (link.href == currentinput.value) {
                        let activitylinksrc = link.closest('.activitytitle');
                        maybeactivityelement = currentinput.parentElement.querySelector(
                            'div.activitytitle.unilabel-input-replacement'
                        );
                        if (maybeactivityelement) {
                            return;
                        }

                        inputswitcher.switchInput(
                            currentinput,
                            null,
                            activitylinksrc,
                            currentinput.value,
                            false,
                            deletestr
                        );
                    }
                });
            }
        } catch (error) {
            log.debug(error);
        }
    });

    $('[data-button="' + formid + '"]').on('click', function(event) {
        event.preventDefault();
        // Get the id (repeat number) from parent element.
        var id = $(this).parent().parent().parent().attr('id').split("_").slice(-1);
        var currentinput = inputidbase + id;
        var currenturltitleinput;
        if (labelidbase == '') {
            currenturltitleinput = '';
        } else {
            currenturltitleinput = labelidbase + id;
        }

        $('#unilabel-modal-activity-picker-' + formid).attr('data-inputid', currentinput);
        $('#unilabel-modal-activity-picker-' + formid).attr('data-labelid', currenturltitleinput);
        $('#unilabel-modal-activity-picker-' + formid).modal('show');
    });
};
