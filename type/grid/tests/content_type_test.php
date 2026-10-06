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

/**
 * Unit tests for the grid content type.
 *
 * @package     unilabeltype_grid
 * @category    test
 * @copyright   2026 ISB Bayern
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class content_type_test extends \advanced_testcase {
    /**
     * Create a grid activity with one tile.
     *
     * @param \stdClass $course
     * @param string $content
     * @return array [course module, tile id]
     */
    private function create_grid_with_tile(\stdClass $course, string $content): array {
        global $DB;
        $unilabel = $this->getDataGenerator()->create_module('unilabel', ['course' => $course->id, 'unilabeltype' => 'grid']);
        $gridid = $DB->insert_record('unilabeltype_grid', (object) ['unilabelid' => $unilabel->id, 'columns' => 4]);
        $tileid = $DB->insert_record('unilabeltype_grid_tile', (object) [
            'gridid' => $gridid,
            'title' => 'Tile',
            'content' => $content,
            'visible' => 1,
        ]);
        return [get_coursemodule_from_instance('unilabel', $unilabel->id), $tileid];
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A tile of another grid is not returned for the own course module.
     *
     * @covers \unilabeltype_grid\content_type::get_fragment
     */
    public function test_get_fragment_rejects_foreign_tile(): void {
        $this->resetAfterTest();
        $own = $this->getDataGenerator()->create_course();
        $foreign = $this->getDataGenerator()->create_course();
        [$cm] = $this->create_grid_with_tile($own, 'Own tile');
        [, $foreigntileid] = $this->create_grid_with_tile($foreign, 'Foreign secret');
        $student = $this->getDataGenerator()->create_and_enrol($own, 'student');
        $this->setUser($student);

        $this->expectException(\dml_missing_record_exception::class);
        (new content_type())->get_fragment([
            'contextid' => \context_module::instance($cm->id)->id,
            'cmid' => $cm->id,
            'id' => $foreigntileid,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A tile of the own grid is returned.
     *
     * @covers \unilabeltype_grid\content_type::get_fragment
     */
    public function test_get_fragment_returns_own_tile(): void {
        $this->resetAfterTest();
        $own = $this->getDataGenerator()->create_course();
        [$cm, $tileid] = $this->create_grid_with_tile($own, 'Own tile');
        $student = $this->getDataGenerator()->create_and_enrol($own, 'student');
        $this->setUser($student);

        $content = (new content_type())->get_fragment([
            'contextid' => \context_module::instance($cm->id)->id,
            'cmid' => $cm->id,
            'id' => $tileid,
        ]);
        $this->assertStringContainsString('Own tile', $content);
    }
}
