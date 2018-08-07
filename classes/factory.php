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
 * unilabel module
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel;

defined('MOODLE_INTERNAL') || die();

class factory {

    public static function get_plugin_list() {
        $plugins = self::get_plugins();

        $return = array();
        foreach ($plugins as $name => $plugin) {
            $return[$name] = $plugin->get_name();
        }
        return $return;
    }

    public static function get_plugins() {
        $plugins = \core_component::get_plugin_list('unilabeltype');

        $return = array();
        foreach ($plugins as $name => $notused) {
            $return[$name] = self::get_plugin($name);
        }
        return $return;
    }

    public static function get_plugin($name) : content_type {
        $classname = '\\unilabeltype_'.$name.'\\content_type';
        return new $classname();
    }

    public static function delete_plugin_content($unilabelid) {
        $plugins = self::get_plugins();
        foreach ($plugins as $plugin) {
            $plugin->delete_content($unilabelid);
        }
    }

    public static function save_plugin_content($formdata, $unilabel) {
        $unilabletype = self::get_plugin($unilabel->unilabeltype);

        if ($unilabletype->save_content($formdata, $unilabel)) {
            return true;
        }
        return false;
    }
}
