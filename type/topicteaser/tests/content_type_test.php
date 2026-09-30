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
 * Tests for the section summaries shown by the topic teaser.
 *
 * @package    unilabeltype_topicteaser
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \unilabeltype_topicteaser\content_type
 */
final class content_type_test extends \advanced_testcase {
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Active content in a section summary of the referenced course must be removed.
     */
    public function test_section_summary_script_is_removed(): void {
        $summary = $this->get_rendered_summary('<p>Teaser</p><img src="x" onerror="alert(1)"><script>alert(2)</script>');

        $this->assertStringNotContainsString('onerror', $summary);
        $this->assertStringNotContainsString('<script', $summary);
        $this->assertStringContainsString('<p>Teaser</p>', $summary);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * Regular summary markup of the referenced course is kept.
     */
    public function test_section_summary_markup_is_kept(): void {
        $summary = $this->get_rendered_summary('<p><strong>Wichtig</strong></p><ul><li>Punkt</li></ul>');

        $this->assertStringContainsString('<strong>Wichtig</strong>', $summary);
        $this->assertStringContainsString('<li>Punkt</li>', $summary);
    }

    /**
     * Render the topic teaser sections of another course and return the summary of its first section.
     *
     * @param string $summary Summary of section 1 in the referenced course
     * @return string Rendered summary
     */
    private function get_rendered_summary(string $summary): string {
        global $DB, $PAGE;
        $this->resetAfterTest();
        $this->setAdminUser();

        $sourcecourse = $this->getDataGenerator()->create_course(['numsections' => 1]);
        $DB->set_field('course_sections', 'summary', $summary, ['course' => $sourcecourse->id, 'section' => 1]);
        $DB->set_field('course_sections', 'summaryformat', FORMAT_HTML, ['course' => $sourcecourse->id, 'section' => 1]);
        rebuild_course_cache($sourcecourse->id, true);

        $hostcourse = $this->getDataGenerator()->create_course();
        $unilabel = $this->getDataGenerator()->create_module('unilabel', [
            'course' => $hostcourse->id,
            'unilabeltype' => 'topicteaser',
        ]);
        $PAGE->set_course($hostcourse);

        $sections = (new content_type())->get_sections_html($sourcecourse->id, $unilabel);
        return $sections[0]->summary;
    }
}
