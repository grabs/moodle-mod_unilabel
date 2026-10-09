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

namespace unilabeltype_topicteaser;

/**
 * Tests for saving the topic teaser target course.
 *
 * @package    unilabeltype_topicteaser
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \unilabeltype_topicteaser\content_type
 */
final class content_type_test extends \advanced_testcase {
    /**
     * Save the given target course as an editing teacher of the hosting course.
     *
     * @param \stdClass $teacher The editing user.
     * @param \stdClass $unilabel The topic teaser instance.
     * @param int $courseid The submitted target course id.
     * @return int The stored target course id.
     */
    private function save_target_course(\stdClass $teacher, \stdClass $unilabel, int $courseid): int {
        global $DB;
        $prefix = 'unilabeltype_topicteaser_';
        $formdata = (object) [
            $prefix . 'presentation' => 'grid',
            $prefix . 'columns' => 4,
            $prefix . 'defaultmiddle' => 1,
            $prefix . 'defaultsmall' => 1,
            $prefix . 'autorun' => 0,
            $prefix . 'clickaction' => 'opendialog',
            $prefix . 'showintro' => 0,
            $prefix . 'showcoursetitle' => 1,
            $prefix . 'course' => (string) $courseid,
        ];
        $this->setUser($teacher);
        (new content_type())->save_content($formdata, $unilabel);
        return (int) $DB->get_field('unilabeltype_topicteaser', 'course', ['unilabelid' => $unilabel->id]);
    }

    /**
     * A course the editor has no role in is not stored as target.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_save_content_rejects_foreign_course(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $foreigncourse = $generator->create_course(['visible' => 0]);
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $unilabel = $generator->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'topicteaser']);

        $this->assertNotEquals($foreigncourse->id, $this->save_target_course($teacher, $unilabel, $foreigncourse->id));
    }

    /**
     * A course the editor manages is stored as target.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_save_content_accepts_managed_course(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $generator->enrol_user($teacher->id, $othercourse->id, 'editingteacher');
        $unilabel = $generator->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'topicteaser']);

        $this->assertEquals($othercourse->id, $this->save_target_course($teacher, $unilabel, $othercourse->id));
    }
}
