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
     * @param int $carouselid id of the restored carousel
     * @return string stored url
     */
    private function restore_slide_url(string $url, int $carouselid): string {
        global $DB;
        $step = $this->createMock(\restore_activity_structure_step::class);
        $step->method('get_new_parentid')->willReturn($carouselid);
        $subplugin = new \restore_unilabeltype_carousel_subplugin('unilabeltype', 'carousel', $step);
        $subplugin->process_unilabeltype_carousel_slide([
            'id' => 99,
            'url' => $url,
            'urltitle' => 'Title',
            'newwindow' => 0,
            'caption' => '',
        ]);
        return $DB->get_field('unilabeltype_carousel_slide', 'url', ['carouselid' => $carouselid], MUST_EXIST);
    }

    #[Group('baseline')]
    /**
     * A javascript url from a manipulated backup is not stored.
     *
     * @return void
     */
    public function test_restore_check_url(): void {
        $this->resetAfterTest();

        $evilurls = [
            'javascript:0?0:alert(document.domain)',
            'javascript:/*comment*/alert(1)',
            'jav&#x0A;ascript:alert(1)',
            '&#106;avascript:alert(1)',
            'javascript&colon;alert(1)',
            'JaVaScRiPt:alert(1)',
        ];
        $regularurls = [
            'https://example.org/page?a=1',
            'https://example.org/page?a=1&b=2#Anchor',
        ];
        $counter = 1;
        foreach ($evilurls as $evilurl) {
            $this->assertSame('', $this->restore_slide_url($evilurl, $counter));
            $counter++;
        }
        foreach ($regularurls as $regularurl) {
            $this->assertSame($regularurl, $this->restore_slide_url($regularurl, $counter));
            $counter++;
        }
    }

    /**
     * Restore one carousel with the given height and return the stored height.
     *
     * @param string $height height from the backup
     * @param int $unilabelid id of the restored unilabel
     * @return string stored height
     */
    private function restore_carousel_height(string $height, int $unilabelid): string {
        global $DB;
        $step = $this->createMock(\restore_activity_structure_step::class);
        $step->method('get_new_parentid')->willReturn($unilabelid);
        $subplugin = new \restore_unilabeltype_carousel_subplugin('unilabeltype', 'carousel', $step);
        $subplugin->process_unilabeltype_carousel([
            'id' => 99,
            'carouselinterval' => 5,
            'height' => $height,
            'background' => '#ffffff',
            'captionstyle' => 'dark',
            'captionwidth' => 0,
            'showintro' => 0,
            'usemobile' => 0,
        ]);
        return $DB->get_field('unilabeltype_carousel', 'height', ['unilabelid' => $unilabelid], MUST_EXIST);
    }

    #[Group('baseline')]
    /**
     * A height with markup from a manipulated backup is stored as number.
     *
     * @return void
     */
    public function test_restore_cleans_height(): void {
        $this->resetAfterTest();

        // The evil height should be converted to the numeric "0".
        $evilheight = '0;width:0" onmouseover="alert(1)';
        $this->assertSame('0', $this->restore_carousel_height($evilheight, 1));

        $regularheight = '300';
        $this->assertSame($regularheight, $this->restore_carousel_height($regularheight, 2));
    }
}
