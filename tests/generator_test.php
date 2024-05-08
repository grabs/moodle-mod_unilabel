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

/**
 * Unilabel type imageboard
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_imageboard;

/**
 * Unit tests for generating instances.
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class generator_test extends \advanced_testcase {

    /**
     * Test create an instance
     *
     * @covers ::unilabel_add_instance()
     * @return void
     */
    public function test_create_instance(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('unilabel', ['course' => $course->id]));
        $unilabel = $this->getDataGenerator()->create_module(
            'unilabel',
            [
                'course'       => $course,
                'idnumber'     => 'mh1',
                'name'         => 'testlabel',
                'intro'        => 'Hello label',
                'unilabeltype' => 'imageboard',
            ]
        );
        $records = $DB->get_records('unilabel', ['course' => $course->id, 'unilabeltype' => 'imageboard'], 'id');
        $this->assertEquals(1, count($records));
        $this->assertTrue(array_key_exists($unilabel->id, $records));

        $params = [
            'course'       => $course->id,
            'idnumber'     => 'mh2',
            'name'         => 'testlabel2',
            'intro'        => 'Hello label-2',
            'unilabeltype' => 'imageboard',
        ];
        $unilabel = $this->getDataGenerator()->create_module('unilabel', $params);
        $records = $DB->get_records('unilabel', ['course' => $course->id, 'unilabeltype' => 'imageboard'], 'id');
        $this->assertEquals(2, count($records));
        $this->assertEquals('testlabel2', $records[$unilabel->id]->name);
    }
}
