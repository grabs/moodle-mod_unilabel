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

import ContentLoader from 'unilabeltype_grid/contentloader';
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
import log from 'core/log';

// Register the del button and get the html from mustache.
const registerDelButton = (headerelement, index) => {
    const context = {
        repeatindex: index,
        repeatnr: (index + 1)
    };
    return Templates.renderForPromise('unilabeltype_grid/delete_element_button', context)
    .then(({html, js}) => {
        headerelement.querySelector('div.d-flex').insertAdjacentHTML(
            'beforeend', html
        );
        Templates.runTemplateJS(js);
        return;
    }).catch((error) => displayException(error));
};

const delElement = (index) => {
    var myelements = [
        'unilabeltype_grid_title',
        'unilabeltype_grid_url',
        'unilabeltype_grid_image',
        'unilabeltype_grid_image_mobile'
    ];
    var myeditorelements = [
        'unilabeltype_grid_content'
    ];
    var headerelement = document.querySelector('#id_singleelementheader_' + index);
    if (headerelement) {
        headerelement.remove();
    }
    var myparent = document.querySelector('#id_unilabelcontenthdr');
    if (myparent) {
        var newelement;
        myelements.forEach((element) => {
            newelement = document.createElement('input');
            newelement.type = 'hidden';
            newelement.name = element + '[' + index + ']';
            newelement.value = '';
            myparent.insertAdjacentElement('afterbegin', newelement);
        });
        myeditorelements.forEach((element) => {
            newelement = document.createElement('input');
            newelement.type = 'hidden';
            newelement.name = element + '[' + index + '][text]';
            newelement.value = '';
            myparent.insertAdjacentElement('afterbegin', newelement);
            newelement = document.createElement('input');
            newelement.type = 'hidden';
            newelement.name = element + '[' + index + '][format]';
            newelement.value = 1;
            myparent.insertAdjacentElement('afterbegin', newelement);
            newelement = document.createElement('input');
            newelement.type = 'hidden';
            newelement.name = element + '[' + index + '][itemid]';
            newelement.value = 0;
            myparent.insertAdjacentElement('afterbegin', newelement);
        });
    }
};

// Export our init method.
export const init = (formid, contextid, courseid, prefix) => {
    // Register a click for the whole form but only applying to the delButtons.
    var thisform = document.querySelector('#' + formid);
    thisform.addEventListener('click', (e) => {
        if (e.target.dataset.action == 'deleteelement') {
            var index = e.target.dataset.id;
            log.debug('Deleting element: ' + index);
            delElement(index);
        }
    });

    // Look for the header elements and add and register a delete button.
    var headerelements = document.querySelectorAll('fieldset[id^="id_singleelementheader"]');
    for (var i = 0; i < headerelements.length; i++) {
        var headerelement = headerelements[i];
        log.debug('looking for: ' + headerelement.id);
        registerDelButton(headerelement, i);
    }

    var button = document.querySelector('#button-' + formid);
    if (button) {
        var repeatbutton = document.querySelector('#fitem_id_' + prefix + 'add_more_elements_btn');
        if (repeatbutton) {
            repeatbutton.remove();
        }
        button.addEventListener('click', (e) => {
            var contentcontainerselector = '#addcontent-' + formid;
            var fragmentcall = 'get_html';
            var serviceparams = {
                'contextid': contextid,
                'formid': formid,
                'courseid': courseid,
                'prefix': prefix
            };

            var repeatindex = parseInt(e.target.form.unilabeltype_grid_chosen_elements_count.value);
            e.target.form.unilabeltype_grid_chosen_elements_count.value = repeatindex + 1;
            serviceparams.repeatindex = repeatindex;
            log.debug(serviceparams);

            var contentLoader = new ContentLoader(contentcontainerselector, fragmentcall, serviceparams, contextid);
            contentLoader.loadContent('beforebegin');
        });
    }
};
