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

import $ from 'jquery';
import log from 'core/log';

export const init = async(formid) => {
    let currentinput;
    let currenturltitleinput;
    let maybeactivityelement;
    let modalid = 'unilabel-modal-activity-picker-' + formid;
    const str = await import('core/str');
    const deletestr = await str.get_string('delete');
    const inputswitcher = await import('mod_unilabel/activity_picker_input_switcher');

    $('#' + modalid).on('show.bs.modal', function() {
        $('#' + modalid).appendTo('body');
        currentinput = document.querySelector('#' + document.querySelector('#' + modalid).dataset.inputid);

        if (document.querySelector('#' + modalid).dataset.labelid == '') {
            currenturltitleinput = null; // Url title field does not exist.
        } else {
            currenturltitleinput = document.querySelector('#' + document.querySelector('#' + modalid).dataset.labelid);
        }
        maybeactivityelement = currentinput.parentElement.querySelector('div.activitytitle.unilabel-input-replacement');
    });

    document.querySelector('#unilabel-activity-picker').addEventListener('click', (e) => {
        if (!e.target.classList.contains('activity-picker-link')) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();

        if (maybeactivityelement) {
            log.debug('There already is an replacement element. It must be remove before a new one is added.');
            maybeactivityelement.remove();
        }

        $('#unilabel-modal-activity-picker-' + formid).modal('hide');
        if (e.target.classList.contains('activity-picker-link')) {
            let url = e.target.href;
            let activitylinksrc = e.target.closest('.activitytitle');
            inputswitcher.switchInput(currentinput, currenturltitleinput, activitylinksrc, url, true, deletestr);
        }
    });

    $("#search-" + formid).on("keyup", function() {
        let value = $(this).val().toLowerCase();
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
};
