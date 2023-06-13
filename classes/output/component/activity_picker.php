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
 * mod_unilabel Activity picker
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace mod_unilabel\output\component;

/**
 * Activity picker to choose a internal url to an activity.
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_picker implements \renderable, \templatable {
    /** @var array */
    public $data;

    /**
     * Constructor
     *
     * @param \stdClass $course
     * @param string $formid
     */
    public function __construct(\stdClass $course, $formid) {
        $this->data = array();
        $activities = array();
        $info = get_fast_modinfo($course);
        $strstealthinfo = get_string('hiddenoncoursepage');
        $strhiddeninfo = get_string('hiddenfromstudents');

        if ($coursemodules = $info->get_cms()) {
            foreach ($coursemodules as $cm) {
                if ($cm->deletioninprogress || (!$cm->has_view())) {
                    continue;
                }

                $activityinfo = new \stdClass();
                $activityinfo->activityname = $cm->get_name();
                $activityinfo->url = $cm->get_url();
                $activityinfo->modstealth = $cm->is_stealth();
                $activityinfo->stealthinfo = $strstealthinfo;
                $activityinfo->hidden = (!$cm->visible) || $cm->is_stealth();
                $activityinfo->hiddeninfo = $strhiddeninfo;
                $activityinfo->availability = $cm->availableinfo;
                $activityinfo->icon = $cm->get_icon_url();
                $activityinfo->module = $cm->modname;
                $activityinfo->modulename = $cm->get_module_type_name();
                $activityinfo->purpose = $purpose = plugin_supports('mod', $cm->modname, FEATURE_MOD_PURPOSE, 'none');
                $activityinfo->filterstring = $cm->get_name() . ' ' . $cm->get_module_type_name();
                $activities[] = $activityinfo;
            }
            $this->data['hasactivities'] = true;
        }

        $this->data['activities'] = $activities;
        $this->data['formid'] = $formid;
    }

    /**
     * Export the data for usage in mustache.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
