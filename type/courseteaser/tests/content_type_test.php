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

namespace unilabeltype_courseteaser;

/**
 * Tests for saving and rendering the teased courses.
 *
 * @package    unilabeltype_courseteaser
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \unilabeltype_courseteaser\content_type
 */
final class content_type_test extends \advanced_testcase {
    /**
     * Save the given courses as the given editor.
     *
     * @param \stdClass $teacher The editing user.
     * @param \stdClass $unilabel The course teaser instance.
     * @param array $courseids The submitted course ids.
     * @return string The stored course ids.
     */
    private function save_courses(\stdClass $teacher, \stdClass $unilabel, array $courseids): string {
        global $DB;
        $prefix = 'unilabeltype_courseteaser_';
        $formdata = (object) [
            $prefix . 'presentation' => 'grid',
            $prefix . 'columns' => 4,
            $prefix . 'defaultmiddle' => 1,
            $prefix . 'defaultsmall' => 1,
            $prefix . 'autorun' => 0,
            $prefix . 'showintro' => 0,
            $prefix . 'courses' => array_map('strval', $courseids),
        ];
        $this->setUser($teacher);
        (new content_type())->save_content($formdata, $unilabel);
        return (string) $DB->get_field('unilabeltype_courseteaser', 'courses', ['unilabelid' => $unilabel->id]);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A course the editor has no role in is neither stored nor rendered.
     */
    public function test_foreign_course_is_not_stored_or_rendered(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $foreigncourse = $generator->create_course(['visible' => 0]);
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $student = $generator->create_and_enrol($course, 'student');
        $unilabel = $generator->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'courseteaser']);

        $this->assertSame((string) $course->id, $this->save_courses($teacher, $unilabel, [$course->id, $foreigncourse->id]));

        // A value stored before the check must not reveal the hidden course either.
        $DB->set_field('unilabeltype_courseteaser', 'courses', $foreigncourse->id, ['unilabelid' => $unilabel->id]);
        $this->setUser($student);
        $this->assertSame([], (new content_type())->get_course_infos($unilabel));
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Courses the editor manages are stored and rendered.
     */
    public function test_managed_courses_are_stored_and_rendered(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $generator->enrol_user($teacher->id, $othercourse->id, 'editingteacher');
        $unilabel = $generator->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'courseteaser']);

        $stored = $this->save_courses($teacher, $unilabel, [$course->id, $othercourse->id]);

        $this->assertSame($course->id . ',' . $othercourse->id, $stored);
        $this->assertCount(2, (new content_type())->get_course_infos($unilabel));
    }
}
