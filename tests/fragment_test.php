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

namespace mod_unilabel;

/**
 * Unit tests for the fragment callbacks of mod_unilabel.
 *
 * @package     mod_unilabel
 * @category    test
 * @copyright   2026 ISB Bayern
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fragment_test extends \advanced_testcase {
    /**
     * Create a grid activity and a user enrolled with the given role.
     *
     * @param string $role
     * @return \context_module
     */
    private function setup_grid_as(string $role): \context_module {
        global $CFG;
        require_once($CFG->dirroot . '/mod/unilabel/lib.php');
        $course = $this->getDataGenerator()->create_course();
        $unilabel = $this->getDataGenerator()->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'grid']);
        $this->setUser($this->getDataGenerator()->create_and_enrol($course, $role));
        return \context_module::instance($unilabel->cmid);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A user with the edit capability gets the edit element.
     *
     * @covers ::mod_unilabel_output_fragment_get_edit_element
     */
    public function test_get_edit_element_as_teacher(): void {
        global $PAGE;
        $this->resetAfterTest();
        $context = $this->setup_grid_as('editingteacher');
        $PAGE->set_url('/mod/unilabel/edit_content.php');

        $html = mod_unilabel_output_fragment_get_edit_element([
            'contextid' => $context->id,
            'formid' => 'testform',
            'repeatindex' => 0,
        ]);
        $this->assertNotEmpty($html);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A user without the edit capability does not get the edit element.
     *
     * @covers ::mod_unilabel_output_fragment_get_edit_element
     */
    public function test_get_edit_element_as_student(): void {
        $this->resetAfterTest();
        $context = $this->setup_grid_as('student');

        $this->expectException(\required_capability_exception::class);
        mod_unilabel_output_fragment_get_edit_element([
            'contextid' => $context->id,
            'formid' => 'testform',
            'repeatindex' => 0,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A user without the edit capability does not get the editor configuration.
     *
     * @covers ::mod_unilabel_output_fragment_get_tinyconfig
     */
    public function test_get_tinyconfig_as_student(): void {
        $this->resetAfterTest();
        $context = $this->setup_grid_as('student');

        $this->expectException(\required_capability_exception::class);
        mod_unilabel_output_fragment_get_tinyconfig([
            'contextid' => $context->id,
            'targetid' => 'id_unilabeltype_grid_content_0',
            'targetname' => 'unilabeltype_grid_content[0]',
            'draftitemid' => 1,
            'repeatindex' => 0,
        ]);
    }
}
