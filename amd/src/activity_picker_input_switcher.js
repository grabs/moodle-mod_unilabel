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

import log from 'core/log';

/**
 * Switch the input to a visible activity link.
 *
 * @param {Element} currentinput The url input field which is bound to the picker
 * @param {Element} activitylinksrc The activity element from the picker list to be cloned to the url input
 * @param {String} url
 * @param {Boolean} makedirty Should the from be dirty after switching
 * @param {String} deletestr The delete string for the delete button.
 */
export const switchInput = (currentinput, activitylinksrc, url, makedirty, deletestr) => {
    /**
     * Go recursive through all child elements given from element and apply the callback function.
     *
     * @param {Element} element
     * @param {CallableFunction} callback
     */
    const childrenAll = (element, callback) => {
        for (const child of element.children) {
            childrenAll(child, callback);
        }
        callback(element);
    };

    /**
     * Make the form dirty so the changechecker is aware of.
     *
     * @param {Element} currentinput
     */
    const makeFormDirty = (currentinput) => {
        // To make the moodle form aware of the change, we set the data-initial-value to its original value.
        currentinput.closest('form').dataset.formDirty = true;
    };

    currentinput.value = url;
    currentinput.type = 'hidden';
    currentinput.dataset.initialValue = currentinput.value;
    let activitylink = activitylinksrc.closest('.activitytitle').cloneNode(true); // The new clone might have ids.

    log.debug('Remove all links from clone');
    childrenAll(activitylink, (e) => {
        e.removeAttribute('id'); // Remove all ids.
        if (e.nodeName.toLowerCase() == 'div' && e.classList.contains('unilabel-activity-picker-info')) {
            e.remove();
        }
        if (e.nodeName.toLowerCase() == 'a') {
            e.target = '_blank';
            e.classList.remove('stretched-link');
        }
    });

    activitylink.classList.add('border', 'unilabel-input-replacement');
    let deletelinkcontainer = document.createElement('div');
    let deletelink = document.createElement('a');
    let deleteicon = document.createElement('i');

    deleteicon.classList.add('fa', 'fa-times', 'text-danger');
    deleteicon.dataset.inputid = currentinput.id; // Add data attribute because it often is the click target.
    deleteicon.title = deletestr;

    deletelink.insertAdjacentElement('afterbegin', deleteicon);
    deletelink.href = '#';
    deletelink.dataset.inputid = currentinput.id; // Add the data attribute to find the input field.
    deletelink.title = deletestr;

    deletelinkcontainer.insertAdjacentElement('afterbegin', deletelink);
    activitylink.insertAdjacentElement('beforeend', deletelinkcontainer);
    currentinput.insertAdjacentElement('afterend', activitylink);

    if (makedirty) {
        makeFormDirty(currentinput);
    }

    deletelink.addEventListener('click', (e) => {
        e.preventDefault();
        activitylink.remove();
        let currentinput = document.querySelector('#' + e.target.dataset.inputid);
        currentinput.value = '';
        currentinput.type = 'text';
        currentinput.focus();

        makeFormDirty(currentinput);
    });
};
