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

abstract class content_type {
    abstract public function get_namespace();

    abstract public function get_content($unilabel, $cm, \plugin_renderer_base $renderer);
    abstract public function delete_content($unilabelid);
    abstract public function add_form_fragment(edit_content_form $form, \context $context);
    abstract public function get_form_default($data, $unilabel);
    abstract public function save_content($formdata, $unilabel);

    public function format_intro($unilabel, $cm) {
        return $intro = format_module_intro('unilabel', $unilabel, $cm->id, false);
    }

    public function get_name() {
        return get_string('pluginname', $this->get_namespace());
    }

    function form_validation($errors, $data, $files) {
        return $errors;
    }
}
