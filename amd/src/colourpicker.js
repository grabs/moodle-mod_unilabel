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
 * unilabel helper for colour picker
 *
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param {string} clickid
 * @param {string} hiddeninputid
 * @param {string} inputid
 */
export const init = (clickid, hiddeninputid, inputid) => {
    const clickElement = document.querySelector("#" + clickid);
    const selectElement = document.querySelector("#" + hiddeninputid);
    const result = document.querySelector("#" + inputid);
    clickElement.addEventListener("click", () => {
        result.value = selectElement.value;
    });
};
