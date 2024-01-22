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
 * unilabel type accordion.
 *
 * @package     unilabeltype_accordion
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_accordion\output;

/**
 * Content type definition.
 * @package     unilabeltype_accordion
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_element extends \mod_unilabel\output\edit_element_base {

    /**
     * Constructor
     *
     * @param string $formid The id the edit_content form (mform) is using
     * @param \context $context The context of the cm
     * @param \stdClass $course
     * @param string $type The unilabel type like "grid" or "carousel"
     * @param int $repeatindex
     */
    public function __construct(string $formid, \context $context, \stdClass $course, string $type, int $repeatindex) {
        global $OUTPUT;

        parent::__construct($formid, $context, $course, $type, $repeatindex);

        $this->data->headingelement = $this->get_editor('heading', ['rows' => 2], $this->editor_options(), 'heading');
        $this->data->contentelement = $this->get_editor('content', ['rows' => 10], $this->editor_options(), 'content');
        $this->data->sortorderelement = $this->get_hidden('sortorder');

    }

    /**
     * Export for template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }

    /**
     * Get the options array to support files in editor.
     *
     * @return array
     */
    public function editor_options() {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean'  => true,
            'context'  => $this->context,
            'subdirs'  => true,
        ];
    }

}
