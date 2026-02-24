<?php
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
 * unilabel module.
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel;

/**
 * Completion helper class
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_info extends \completion_info {
    /**
     * Marks a module as viewed and updates its completion status.
     *
     * This method sets the viewed status for a course module and triggers completion
     * if the view completion requirement is enabled. It also dispatches a JavaScript
     * event for manual completion toggling.
     * Note: We override the parents method "set_module_viewed()" because it throws an error,
     * if the page already printed its header.
     * Instead we use parts from this method and dispatch a javascript custom event to refresh the courseindex.
     *
     * @param \stdClass $cm The course module object containing completion settings and module information.
     * @param int $userid The ID of the user viewing the module. Defaults to 0 (current user).
     * @return void
     */
    public function set_module_viewed($cm, $userid = 0) {
        global $PAGE, $DB;

        // Don't do anything if view condition is not turned on.
        if ($cm->completionview == COMPLETION_VIEW_NOT_REQUIRED || !$this->is_enabled($cm)) {
            return;
        }

        // Get current completion state.
        $data = $this->get_data($cm, false, $userid);

        // If we already viewed it, don't do anything unless the completion status is overridden.
        // If the completion status is overridden, then we need to allow this 'view' to trigger automatic completion again.
        if ($data->viewed == COMPLETION_VIEWED && empty($data->overrideby)) {
            return;
        }

        // OK, change state, save it, and update completion.
        $data->viewed = COMPLETION_VIEWED;
        $this->internal_set_data($cm, $data);
        $this->update_state($cm, COMPLETION_COMPLETE, $userid);

        $unilabelname  = $DB->get_field('unilabel', 'name', ['id' => $cm->instance]);
        // Call the togglecompletion javascript, which dispatch the custom event "CourseEvents.manualCompletionToggled".
        $PAGE->requires->js_call_amd('mod_unilabel/togglecompletion', 'init', [$cm->id, $unilabelname, true]);
    }
}
