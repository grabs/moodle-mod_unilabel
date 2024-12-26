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
 * @package     unilabeltype_carousel
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
function xmldb_unilabeltype_carousel_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023121501) {

        // Define field sortorder to be added to unilabeltype_carousel_tile.
        $table = new xmldb_table('unilabeltype_carousel_slide');
        $field = new xmldb_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'carouselid');

        // Conditionally launch add field sortorder.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Carousel savepoint reached.
        upgrade_plugin_savepoint(true, 2023121501, 'unilabeltype', 'carousel');
    }

    if ($oldversion < 2024012400) {

        // Define field sortorder to be added to unilabeltype_carousel_slide.
        $table = new xmldb_table('unilabeltype_carousel_slide');
        $field = new xmldb_field('newwindow', XMLDB_TYPE_INTEGER, '1', null, null, null, null, 'url');

        // Conditionally launch add field newwindow.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Carousel savepoint reached.
        upgrade_plugin_savepoint(true, 2024012400, 'unilabeltype', 'carousel');
    }

    if ($oldversion < 2024050700) {

        // Define field captionstyle to be added to unilabeltype_carousel.
        $table = new xmldb_table('unilabeltype_carousel');
        $field = new xmldb_field('captionstyle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'background');

        // Conditionally launch add field captionstyle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field captionwidth to be added to unilabeltype_carousel.
        $table = new xmldb_table('unilabeltype_carousel');
        $field = new xmldb_field('captionwidth', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'captionstyle');

        // Conditionally launch add field captionwidth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Carousel savepoint reached.
        upgrade_plugin_savepoint(true, 2024050700, 'unilabeltype', 'carousel');
    }

    if ($oldversion < 2024122600) {

        // Define field urltitle to be added to unilabeltype_carousel_slide.
        $table = new xmldb_table('unilabeltype_carousel_slide');
        $field = new xmldb_field('urltitle', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'caption');

        // Conditionally launch add field urltitle.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Carousel savepoint reached.
        upgrade_plugin_savepoint(true, 2024122600, 'unilabeltype', 'carousel');
    }

    return true;
}
