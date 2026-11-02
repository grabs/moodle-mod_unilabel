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

namespace unilabeltype_collapsedtext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests for the collapsed text content type.
 *
 * @package    unilabeltype_collapsedtext
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(content_type::class)]
final class content_type_test extends \advanced_testcase {
    /**
     * Render a collapsed text with text filters enabled and the given name.
     *
     * @param string $name stored activity name
     * @return string
     */
    private function render_with_filters(string $name): string {
        global $DB, $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $unilabel = $this->getDataGenerator()->create_module('unilabel', [
            'course' => $course->id,
            'unilabeltype' => 'collapsedtext',
        ]);
        $DB->set_field('unilabel', 'name', $name, ['id' => $unilabel->id]);
        $DB->insert_record('unilabeltype_collapsedtext', (object) [
            'unilabelid' => $unilabel->id,
            'presentation' => 'collapsed',
            'useanimation' => 0,
            'applytextfilters' => 1,
        ]);
        $cm = get_coursemodule_from_instance('unilabel', $unilabel->id);
        $PAGE->set_url('/course/view.php', ['id' => $course->id]);
        $PAGE->set_context(\context_course::instance($course->id));
        $type = new content_type();
        return $type->get_content($DB->get_record('unilabel', ['id' => $unilabel->id]), $cm, $PAGE->get_renderer('mod_unilabel'));
    }

    #[Group('baseline')]
    /**
     * Active markup in the activity name is removed.
     *
     * @return void
     */
    public function test_filtered_title_is_cleaned(): void {
        $this->resetAfterTest();

        $evilhtml = 'Chapter 1<img src=x onerror=alert(1)>';
        $cleanedhtml = 'Chapter 1';
        $html = $this->render_with_filters($evilhtml);

        $this->assertStringContainsString($cleanedhtml, $html);
        $this->assertStringNotContainsString($evilhtml, $html);
    }

    #[Group('baseline')]
    /**
     * Harmless markup in the activity name is kept.
     *
     * @return void
     */
    public function test_filtered_title_keeps_harmless_markup(): void {
        $this->resetAfterTest();

        $harmlesshtml = 'Chapter <strong>1</strong>';
        $this->assertStringContainsString($harmlesshtml, $this->render_with_filters($harmlesshtml));
    }
}
