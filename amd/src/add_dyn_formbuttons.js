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

import ContentLoader from 'mod_unilabel/contentloader';
import Templates from 'core/templates';
import notification from 'core/notification';
import log from 'core/log';

let _formid;
let _type;
let _elements;

/**
 * Register the del button and get the html from mustache.
 *
 * @param {Element} headerelement The draggable header element
 * @param {Boolean} useVisibility Show visible button or not.
 * @param {Integer} index The index of the headerelement
 * @returns {Promise}
 */
const registerActionButtons = (headerelement, useVisibility, index) => {
    const context = {
        type: _type,
        repeatindex: index,
        repeatnr: (index + 1),
    };
    return Templates.renderForPromise('mod_unilabel/element_action_buttons', context)
    .then(({html, js}) => {
        headerelement.querySelector('div.d-flex').insertAdjacentHTML(
            'beforeend', html
        );
        if (useVisibility) {
            initVisibleButton(headerelement, index);
        }
        Templates.runTemplateJS(js);
        return;
    }).catch((error) => notification.exception(error));
};

/**
 * Initilize the visible button.
 *
 * @param {Element} headerelement The collapseble form section header
 * @param {Integer} index The repeatindex of the element
 */
const initVisibleButton = (headerelement, index) => {
    headerelement.classList.add('has-visibility');
    var visibleElement = document.getElementsByName('unilabeltype_' + _type + '_visible[' + index + ']')[0];
    if (!visibleElement) {
        return;
    }
    var visibleButton = headerelement.querySelector('a.visible-button i');
    if (!visibleButton) {
        return;
    }
    setVisibilityStyle(visibleButton, visibleElement.value == '1');
};

/**
 * Set the icon of the visible button
 *
 * @param {Element} visibleButton The visible button element
 * @param {Boolean} enabled Show as enabled or disabled
 */
const setVisibilityStyle = (visibleButton, enabled) => {
    if (enabled) {
        visibleButton.classList.remove('fa-eye-slash');
        visibleButton.classList.add('fa-eye');
        visibleButton.closest('fieldset').classList.remove('visible-off');
    } else {
        visibleButton.classList.remove('fa-eye');
        visibleButton.classList.add('fa-eye-slash');
        visibleButton.closest('fieldset').classList.add('visible-off');
    }
};

/**
 * Switch the visiblity of the repeat elment with the given index.
 *
 * @param {Integer} index The repeatindex of the element we want to switch.
 */
const switchElementVisible = (index) => {
    log.debug('Set element visible/visible-off: ' + index);

    // Get the hidden input for the visiblity state.
    var visibleElement = document.getElementsByName('unilabeltype_' + _type + '_visible[' + index + ']')[0];
    if (!visibleElement) {
        return;
    }
    // Switch the visibility state.
    visibleElement.value = visibleElement.value == '1' ? '0' : '1';

    // Now we have to change the icon of the visible button.
    var visibleButton = document.querySelector('#id_singleelementheader_' + index + ' a.visible-button i');
    if (!visibleButton) {
        return;
    }
    setVisibilityStyle(visibleButton, visibleElement.value == '1');
};

/**
 * Delete an element and set dummy hidden elements with "0" value, what is needed by the mform.
 *
 * @param {Integer} index The index of the deleted element
 */
const delElement = (index) => {
    var headerelement = document.querySelector('#id_singleelementheader_' + index);
    if (headerelement) {
        headerelement.remove();
    }
    var thisform = document.querySelector('#' + _formid);
    var myparent = document.querySelector('#id_unilabelcontenthdr');
    if (myparent) {
        var newelement;
        _elements.forEach((element) => {
            let name = 'unilabeltype_' + _type + '_' + element + '[' + index + ']';
            log.debug('Set dummy element ' + name);
            newelement = document.createElement('input');
            newelement.type = 'hidden';
            newelement.name = name;
            newelement.value = '';
            myparent.insertAdjacentElement('afterbegin', newelement);
        });
        const myevent = new CustomEvent('itemremoved', {detail: index});
        thisform.dispatchEvent(myevent);
    }
};

/**
 * Export our init method.
 *
 * @param {string} type The type of unilabeltype e.g.: grid
 * @param {string} formid The id of the mform the draggable elements are related to
 * @param {Integer} contextid
 * @param {string} prefix
 * @param {array} elements The dummy fields we need if we want to delete an element
 * @param {boolean} useDragdrop The same as element but for editor which has subelements like "text", "format" and "itemid"
 * @param {boolean} useVisibility Should we show the visible button?
 */
export const init = async(type, formid, contextid, prefix, elements, useDragdrop, useVisibility) => {
    // Import the dragdrop module asynchron.
    const dragDrop = await import('mod_unilabel/dragdrop');
    dragDrop.init(type, formid, useDragdrop);

    _type = type;
    _formid = formid;
    _elements = elements;

    // Register a click for the whole form but only applying to the delButtons.
    var thisform = document.querySelector('#' + formid);
    thisform.addEventListener('click', (e) => {
        var index;
        if (e.target.dataset.action == 'shoulddelete') {
            e.preventDefault();
        }
        if (e.target.dataset.action == 'deleteelement') {
            index = e.target.dataset.id;
            log.debug('Deleting element: ' + index);
            delElement(index);
        }
        if (e.target.dataset.action == 'setvisible') {
            e.preventDefault();
            index = e.target.dataset.id;
            switchElementVisible(index);
            // To make the moodle form aware of the change, we set the data-initial-value to its original value.
            log.debug('GRABS:');
            log.debug(e);
            e.target.closest('form').dataset.formDirty = true;
        }
    });

    // Look for the header elements and add and register a delete button.
    var headerelements = document.querySelectorAll('fieldset[id^="id_singleelementheader"]');
    for (var i = 0; i < headerelements.length; i++) {
        var headerelement = headerelements[i];
        log.debug('looking for: ' + headerelement.id);
        registerActionButtons(headerelement, useVisibility, i);
    }

    var button = document.querySelector('#button-' + formid);
    if (button) {
        var repeatbutton = document.querySelector('#fitem_id_' + prefix + 'add_more_elements_btn');
        if (repeatbutton) {
            repeatbutton.remove();
        }
        button.addEventListener('click', (e) => {
            var contentcontainerselector = '#addcontent-' + formid;
            var repeatindex = parseInt(e.target.form.multiple_chosen_elements_count.value);
            var fragmentcall = 'get_edit_element';

            var serviceparams = {
                'contextid': contextid,
                'formid': formid,
                'repeatindex': repeatindex
            };
            log.debug(serviceparams);

            e.target.form.multiple_chosen_elements_count.value = repeatindex + 1;

            // To make the moodle form aware of the change, we set the data-initial-value to its original value.
            e.target.form.dataset.formDirty = true;

            var contentLoader = new ContentLoader(contentcontainerselector, fragmentcall, serviceparams, contextid);
            contentLoader.loadContent('beforebegin').then(() => {
                if (useVisibility) {
                    var headerelement = document.querySelector('#id_singleelementheader_' + repeatindex);
                    initVisibleButton(headerelement, repeatindex);
                }
                const myevent = new CustomEvent('itemadded', {detail: repeatindex});
                thisform.dispatchEvent(myevent);
                return true;
            }).catch((error) => notification.exception(error));
        });
    }
};
