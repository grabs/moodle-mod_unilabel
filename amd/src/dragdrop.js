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
 * @author     Andreas Grabs <moodle@grabs-edv.de>
 * @copyright  2024 Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import log from 'core/log';
import config from 'core/config';

var _formid;
var _type;

/**
 * Find the items in our mform we want to be draggable
 *
 * @param {string} formid The id of the mform the draggable items are related toHtml
 * @returns array
 */
const getDraggableItems = (formid) => {
    let fieldsets = document.querySelectorAll('#' + formid + ' fieldset');
    const items = [];
    fieldsets.forEach(fieldset => {
        if (fieldset.id.startsWith('id_singleelementheader_')) {
            items.push(fieldset);
        }
    });
    return items;
};

/**
 * Initialize a draggable item to be ready for drag and drop
 *
 * @param {Element} item The draggable item
 * @param {Integer} index The index of the draggable item
 */
const initDragElement = (item, index) => {
    // Add the class "dragging" a little later to get the dragging image visible.
    item.classList.add('draggable');
    item.dataset.index = index;
};

/**
 * Set the new sortorder values dependig on the current list order.
 */
const resortList = () => {
    // Set the new sortorder;
    let i = 0;
    log.debug('Changed sortorder');
    document.querySelectorAll('#' + _formid + ' fieldset.draggable').forEach(sortitem => {
        let elementindex = sortitem.dataset.index;
        log.debug('Set sortorder in element: ' + 'unilabeltype_' + _type + '_sortorder[' + elementindex + ']');
        let hiddenelement = document.forms[_formid].elements['unilabeltype_' + _type + '_sortorder[' + elementindex + ']'];
        let oldvalue = hiddenelement.value;
        hiddenelement.value = i + 1;
        log.debug('Element: ' + elementindex + ' - old value: ' + oldvalue + ', new value: ' + hiddenelement.value);
        i++;
    });
};

/**
 * Export our init method.
 *
 * @param {string} type The type of unilabeltype e.g.: grid
 * @param {string} formid The id of the mform the draggable elements are related to
 */
export const init = (type, formid) => {
    _type = type;
    _formid = formid;

    // Initialize drag and drop.
    const items = getDraggableItems(formid);
    let index = 0;
    items.forEach(item => {
        initDragElement(item, index);
        index++;
    });

    // Add event listener for new items.
    document.querySelector('#' + formid).addEventListener('itemadded', (e) => {
        log.debug('New element created with index: ' + e.detail);
        var newitem = document.querySelector('#id_singleelementheader_' + e.detail);
        initDragElement(newitem, e.detail);
        resortList();
    });
    // Add event listener if item is removed.
    document.querySelector('#' + formid).addEventListener('itemremoved', (e) => {
        log.debug('Element has been deleted: ' + e.detail);
        resortList();
    });

    // Import Sortable from 'js/Sortable.js';
    return import(config.wwwroot + '/mod/unilabel/js/Sortable.min.js').then((Sortable) => {
        const mysortablelist = document.querySelector('#' + formid);
        var sortable = Sortable.create(
            mysortablelist,
            {
                draggable: '.draggable',
                handle: '.draghandle',
                animation: 150,
                swapThreshold: 0.50,
                onEnd: (e) => {
                    log.debug(e.item);
                    resortList();
                }
            }
        );
        log.debug('Initialized sortable list');
        return sortable;
    });

};
