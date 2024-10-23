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
 * unilabel module.
 *
 * @package     unilabeltype_collapsedtext
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
function xmldb_unilabeltype_collapsedtext_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024050800) {

        // Move all titles from collapsedtext to the unilabel name field.
        $collapsedelements = $DB->get_recordset('unilabeltype_collapsedtext', [], 'id ASC');
        foreach ($collapsedelements as $element) {
            $DB->set_field('unilabel', 'name', $element->title, ['id' => $element->unilabelid]);
        }
        $collapsedelements->close();

        // Define field sortorder to be added to unilabeltype_collapsedtext_tile.
        $table = new xmldb_table('unilabeltype_collapsedtext');
        $field = new xmldb_field('title');

        // Conditionally launch delete field title.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Collapsedtext savepoint reached.
        upgrade_plugin_savepoint(true, 2024050800, 'unilabeltype', 'collapsedtext');
    }
    return true;
}
