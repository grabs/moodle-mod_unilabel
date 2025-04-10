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
 * unilabel type accordion db upgrade.
 *
 * @package     unilabeltype_accordion
 * @copyright   2022 Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param mixed $oldversion
 */

/**
 * Upgrade hook for this plugin.
 *
 * @param  int  $oldversion
 * @return bool
 */
function xmldb_unilabeltype_accordion_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022041602) {
        $table = new xmldb_table('unilabeltype_accordion');
        $field = $table->add_field('type', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2022041602, 'unilabeltype', 'accordion');
    }

    if ($oldversion < 2023111601) {

        // Define field sortorder to be added to unilabeltype_accordion_seg.
        $table = new xmldb_table('unilabeltype_accordion_seg');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'accordionid');

        // Conditionally launch add field sortorder.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Accordion savepoint reached.
        upgrade_plugin_savepoint(true, 2023111601, 'unilabeltype', 'accordion');
    }

    if ($oldversion < 2024050900) {

        // Define field visible to be added to unilabeltype_accordion_seg.
        $table = new xmldb_table('unilabeltype_accordion_seg');
        $field = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'content');

        // Conditionally launch add field visible.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Accordion savepoint reached.
        upgrade_plugin_savepoint(true, 2024050900, 'unilabeltype', 'accordion');
    }

    return true;
}
