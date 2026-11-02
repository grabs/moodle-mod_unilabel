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

namespace unilabeltype_grid;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/mod/unilabel/type/grid/backup/moodle2/restore_unilabeltype_grid_subplugin.class.php');

/**
 * Tests for restoring grid settings.
 *
 * @package    unilabeltype_grid
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\restore_unilabeltype_grid_subplugin::class)]
final class restore_test extends \advanced_testcase {
    /**
     * Restore one grid with the given height and return the stored height.
     *
     * @param string $height height from the backup
     * @return string stored height
     */
    private function restore_grid_height(string $height): string {
        global $DB;
        $step = $this->createMock(\restore_activity_structure_step::class);
        $step->method('get_new_parentid')->willReturn(1);
        $subplugin = new \restore_unilabeltype_grid_subplugin('unilabeltype', 'grid', $step);
        $subplugin->process_unilabeltype_grid([
            'id' => 99,
            'columns' => 4,
            'columnsmiddle' => 2,
            'columnssmall' => 1,
            'height' => $height,
            'showintro' => 0,
            'usemobile' => 0,
            'useajax' => 0,
        ]);
        return $DB->get_field('unilabeltype_grid', 'height', ['unilabelid' => 1], MUST_EXIST);
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
        $this->assertSame('0', $this->restore_grid_height($evilheight));
    }

    #[Group('baseline')]
    /**
     * A regular height is restored unchanged.
     *
     * @return void
     */
    public function test_restore_keeps_regular_height(): void {
        $this->resetAfterTest();

        // The regular height should be kept as is.
        $regularheight = '300';
        $this->assertSame($regularheight, $this->restore_grid_height($regularheight));
    }
}
