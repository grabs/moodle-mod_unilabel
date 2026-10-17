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

namespace unilabeltype_imageboard;

/**
 * Tests for saving imageboard content.
 *
 * @package    unilabeltype_imageboard
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \unilabeltype_imageboard\content_type
 */
final class content_type_test extends \advanced_testcase {
    /**
     * Create an imageboard instance with one stored image.
     *
     * @return array [unilabel, context]
     */
    private function create_imageboard_with_image(): array {
        $course = $this->getDataGenerator()->create_course();
        $unilabel = $this->getDataGenerator()->create_module('unilabel', [
            'course' => $course->id,
            'unilabeltype' => 'imageboard',
        ]);
        $context = \context_module::instance($unilabel->cmid);
        get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'unilabeltype_imageboard',
            'filearea' => 'image',
            'itemid' => 1,
            'filepath' => '/',
            'filename' => 'image.png',
        ], 'image');
        return [$unilabel, $context];
    }

    /**
     * Build the minimal form data for save_content().
     *
     * @param int $cmid The submitted course module id.
     * @return \stdClass
     */
    private function get_formdata(int $cmid): \stdClass {
        $prefix = 'unilabeltype_imageboard_';
        return (object) [
            'cmid' => $cmid,
            $prefix . 'showintro' => 0,
            $prefix . 'canvaswidth' => 600,
            $prefix . 'canvasheight' => 400,
            $prefix . 'autoscale' => 0,
            $prefix . 'titlelineheight' => 2,
            $prefix . 'fontsize' => 12,
            $prefix . 'titlecolor' => '#000000',
            $prefix . 'titlebackgroundcolor' => '#ffffff',
            $prefix . 'backgroundimage' => file_get_unused_draft_itemid(),
            'multiple_chosen_elements_count' => 0,
        ];
    }

    /**
     * A submitted cmid of another activity must not touch that activity's files.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_save_content_ignores_foreign_cmid(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        [$unilabel] = $this->create_imageboard_with_image();
        [$foreignunilabel, $foreigncontext] = $this->create_imageboard_with_image();

        $type = new content_type();
        $this->assertTrue($type->save_content($this->get_formdata($foreignunilabel->cmid), $unilabel));

        $this->assertFalse(get_file_storage()->is_area_empty($foreigncontext->id, 'unilabeltype_imageboard', 'image'));
    }

    /**
     * Saving the own activity still replaces its stored images.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_save_content_uses_own_context(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        [$unilabel, $context] = $this->create_imageboard_with_image();

        $type = new content_type();
        $this->assertTrue($type->save_content($this->get_formdata($unilabel->cmid), $unilabel));

        $this->assertTrue(get_file_storage()->is_area_empty($context->id, 'unilabeltype_imageboard', 'image'));
    }

    /**
     * Render an imageboard with one image linking to the given url.
     *
     * @param string $url
     * @return string
     */
    private function render_imageboard_with_url(string $url): string {
        global $DB, $PAGE;
        $course = $this->getDataGenerator()->create_course();
        $unilabel = $this->getDataGenerator()->create_module('unilabel', [
            'course' => $course->id,
            'unilabeltype' => 'imageboard',
        ]);
        $imageboardid = $DB->insert_record('unilabeltype_imageboard', (object) [
            'unilabelid' => $unilabel->id,
            'canvaswidth' => 600,
            'canvasheight' => 400,
        ]);
        $DB->insert_record('unilabeltype_imageboard_img', (object) [
            'imageboardid' => $imageboardid,
            'title' => 'Image',
            'url' => $url,
        ]);
        $cm = get_coursemodule_from_instance('unilabel', $unilabel->id);
        return (new content_type())->get_content($unilabel, $cm, $PAGE->get_renderer('mod_unilabel'));
    }

    /**
     * A javascript url is not written into the image links.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_get_content_drops_javascript_url(): void {
        $this->resetAfterTest();

        $content = $this->render_imageboard_with_url('javascript:1/1;alert(document.cookie)');

        $this->assertStringNotContainsString('javascript:', $content);
    }

    /**
     * A regular url is rendered as escaped image link.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_get_content_keeps_regular_url(): void {
        $this->resetAfterTest();

        $content = $this->render_imageboard_with_url('https://example.org/page?a=1&b=2');

        $this->assertStringContainsString('href="https://example.org/page?a=1&amp;b=2"', $content);
    }
}
