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

namespace unilabeltype_simpletext;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        return null;
    }

    public function get_form_default($data, $unilabel) {
        return $data;
    }

    public function get_namespace() {
        return __NAMESPACE__;
    }

    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        return $this->format_intro($unilabel, $cm);
    }

    public function delete_content($unilabelid) {
        return true;
    }

    public function save_content($formdata, $unilabel) {
        return true;
    }
}