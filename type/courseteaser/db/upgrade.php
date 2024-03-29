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
 * unilabel type course teaser.
 *
 * @package     unilabeltype_courseteaser
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param mixed $oldversion
 */

/**
 * Upgrade hook for this plugin.
 *
 * @param  int  $oldversion
 * @return bool
 */
function xmldb_unilabeltype_courseteaser_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019030700) {
        // Define field columns to be added to unilabeltype_courseteaser.
        $table = new xmldb_table('unilabeltype_courseteaser');
        $field = new xmldb_field('columns', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'presentation');

        // Conditionally launch add field columns.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Courseteaser savepoint reached.
        upgrade_plugin_savepoint(true, 2019030700, 'unilabeltype', 'courseteaser');
    }

    if ($oldversion < 2019050900) {
        // Define field carouselinterval to be added to unilabeltype_courseteaser.
        $table = new xmldb_table('unilabeltype_courseteaser');
        $field = new xmldb_field('carouselinterval', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'columns');

        // Conditionally launch add field carouselinterval.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add the default carouselinterval to the instances.
        $cfg = get_config('unilabeltype_courseteaser');

        if ($records = $DB->get_records('unilabeltype_courseteaser', null)) {
            foreach ($records as $r) {
                $r->carouselinterval = $cfg->carouselinterval;
                $DB->update_record('unilabeltype_courseteaser', $r);
            }
        }

        // Topicteaser savepoint reached.
        upgrade_plugin_savepoint(true, 2019050900, 'unilabeltype', 'courseteaser');
    }

    if ($oldversion < 2020022900) {
        // Define field columnsmiddle to be added to unilabeltype_courseteaser.
        $table = new xmldb_table('unilabeltype_courseteaser');
        $field = new xmldb_field('columnsmiddle', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'columns');

        // Conditionally launch add field columnsmiddle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field columnssmall to be added to unilabeltype_courseteaser.
        $table = new xmldb_table('unilabeltype_courseteaser');
        $field = new xmldb_field('columnssmall', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'columnsmiddle');

        // Conditionally launch add field columnssmall.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Grid savepoint reached.
        upgrade_plugin_savepoint(true, 2020022900, 'unilabeltype', 'courseteaser');
    }

    return true;
}
