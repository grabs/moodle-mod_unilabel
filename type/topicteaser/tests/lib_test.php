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
 * Tests for the topic teaser file callback.
 *
 * @package    unilabeltype_topicteaser
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /**
     * Create a hidden target course with a section file and a teaser pointing at it.
     *
     * @return array The hosting course, the target section and its course context.
     */
    private function create_teased_section_file(): array {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/unilabel/type/topicteaser/lib.php');

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $target = $generator->create_course(['visible' => 0, 'numsections' => 1]);
        $section = $DB->get_record('course_sections', ['course' => $target->id, 'section' => 1], '*', MUST_EXIST);
        $context = \context_course::instance($target->id);
        get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'course',
            'filearea' => 'section',
            'itemid' => $section->id,
            'filepath' => '/',
            'filename' => 'handout.txt',
        ], 'secret');
        $unilabel = $generator->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'topicteaser']);
        $DB->insert_record('unilabeltype_topicteaser', ['unilabelid' => $unilabel->id, 'course' => $target->id]);

        return [$course, $section, $context];
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A user without access to any teaser of the target course gets no file.
     *
     * @covers ::unilabeltype_topicteaser_pluginfile
     */
    public function test_pluginfile_denies_user_without_teaser(): void {
        $this->resetAfterTest();
        [, $section, $context] = $this->create_teased_section_file();
        $this->setUser($this->getDataGenerator()->create_user());

        $args = [$section->id, 'handout.txt'];
        $this->assertFalse(unilabeltype_topicteaser_pluginfile(null, null, $context, 'section', $args, true));
        $this->assertFalse(unilabeltype_topicteaser_pluginfile(null, null, $context, 'legacy', [0, 'handout.txt'], true));
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A user who can view a teaser of the target course may load its section files.
     *
     * @covers ::unilabeltype_topicteaser_can_serve_section_file
     */
    public function test_section_file_allowed_for_teaser_viewer(): void {
        $this->resetAfterTest();
        [$course, $section, $context] = $this->create_teased_section_file();
        $this->setUser($this->getDataGenerator()->create_and_enrol($course, 'student'));

        $this->assertTrue(unilabeltype_topicteaser_can_serve_section_file($context, 'section', $section->id));
    }
}
