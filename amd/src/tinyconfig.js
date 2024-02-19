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

import fragment from 'core/fragment';
import notification from 'core/notification';

/**
 * Get a configuration object for a specific tinymce editor element.
 *
 * @param {Integer} contextid
 * @param {String} targetid
 * @param {String} targetname
 * @param {Integer} draftitemid
 * @param {Integer} repeatindex
 * @returns {object} The tinymce configuration object
 */
export const getTinyConfig = (contextid, targetid, targetname, draftitemid, repeatindex) => {
    const serviceparams = {
        contextid: contextid,
        targetid: targetid,
        targetname: targetname,
        draftitemid: draftitemid,
        repeatindex: repeatindex
    };
    var fragmentpromise = fragment.loadFragment('mod_unilabel', 'get_tinyconfig', contextid, serviceparams);
    return fragmentpromise.then(function(tinyconfig) {
        return JSON.parse(tinyconfig);
    }).fail(notification.exception);
};
