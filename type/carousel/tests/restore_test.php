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

namespace unilabeltype_carousel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/mod/unilabel/type/carousel/backup/moodle2/restore_unilabeltype_carousel_subplugin.class.php');

/**
 * Tests for restoring carousel slides.
 *
 * @package    unilabeltype_carousel
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\restore_unilabeltype_carousel_subplugin::class)]
final class restore_test extends \advanced_testcase {
    /**
     * Restore one slide with the given url and return the stored url.
     *
     * @param string $url url from the backup
     * @return string stored url
     */
    private function restore_slide_url(string $url): string {
        global $DB;
        $step = $this->createMock(\restore_activity_structure_step::class);
        $step->method('get_new_parentid')->willReturn(1);
        $subplugin = new \restore_unilabeltype_carousel_subplugin('unilabeltype', 'carousel', $step);
        $subplugin->process_unilabeltype_carousel_slide([
            'id' => 99,
            'url' => $url,
            'urltitle' => 'Title',
            'newwindow' => 0,
            'caption' => '',
        ]);
        return $DB->get_field('unilabeltype_carousel_slide', 'url', ['carouselid' => 1], MUST_EXIST);
    }

    #[Group('baseline')]
    /**
     * A javascript url from a manipulated backup is not stored.
     *
     * @return void
     */
    public function test_restore_drops_javascript_url(): void {
        $this->resetAfterTest();

        $this->assertSame('', $this->restore_slide_url('javascript:alert(document.cookie)'));
    }

    #[Group('baseline')]
    /**
     * A regular url is restored unchanged.
     *
     * @return void
     */
    public function test_restore_keeps_regular_url(): void {
        $this->resetAfterTest();

        $this->assertSame('https://example.org/page?a=1', $this->restore_slide_url('https://example.org/page?a=1'));
    }
}
