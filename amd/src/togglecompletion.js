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
 * JavaScript module for updating unilabel completion UI.
 *
 * @module     mod_unilabel/togglecompletion
 * @copyright  2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Import course events for manual completion toggling.
import CourseEvents from 'core_course/events';
// Import course editor functionality to access course state.
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';

/**
 * Initialize the completion toggle functionality.
 *
 * This function creates and dispatches a custom event to update the completion
 * status of a unilabel activity in the course interface.
 *
 * @param {number} cmid - The course module ID of the unilabel activity.
 * @param {string} activityName - The name of the activity being toggled.
 * @param {boolean} completed - The new completion state (true for completed, false for incomplete).
 */
export const init = async(cmid, activityName, completed) => {
    // Create a custom event for manual completion toggling.
    let toggledEvent = new CustomEvent(
        CourseEvents.manualCompletionToggled,
        {
            // Allow the event to bubble up through the DOM tree.
            bubbles: true,
            // Attach detailed information about the completion toggle.
            detail: {
                // The course module ID being toggled.
                cmid: cmid,
                // The name of the activity for display purposes.
                activityname: activityName,
                // The new completion status.
                completed: completed,
                // Currently we ignore the availability.
                withAvailability: false
            }
        }
    );

    // Get the current course editor instance.
    let editor = getCurrentCourseEditor();
    // Wait for the course state to be loaded from the server before proceeding.
    await editor.getServerCourseState();

    // Get the moodle page element where the event should be dispatched.
    let page = document.getElementById('page');
    // Dispatch the completion toggle event if the page element exists.
    if (page) {
        page.dispatchEvent(toggledEvent);
    }
};
