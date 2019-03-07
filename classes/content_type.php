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

/**
 * General class to define a content type.
 * This class is used in all sub plugins "unilabeltype".
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class content_type {
    /**
     * Get the namespace of the content type class.
     *
     * @return string
     */
    abstract public function get_namespace();

    /**
     * Get the html output of the content type
     *
     * @param \stdClass $unilabel
     * @param \stdClass $cm
     * @param \plugin_renderer_base $renderer
     * @return string
     */
    abstract public function get_content($unilabel, $cm, \plugin_renderer_base $renderer);

    /**
     * Delete the content from database.
     *
     * @param int $unilabelid
     * @return void
     */
    abstract public function delete_content($unilabelid);

    /**
     * Add form elements needed by the content type class.
     *
     * @param edit_content_form $form
     * @param \context $context
     * @return void
     */
    abstract public function add_form_fragment(edit_content_form $form, \context $context);

    /**
     * Get all default values for the content type used in the settings form.
     *
     * @param array $data
     * @param \stdClass $unilabel
     * @return array
     */
    abstract public function get_form_default($data, $unilabel);

    /**
     * Save the content into the database.
     *
     * @param \stdClass $formdata
     * @param \stdClass $unilabel
     * @return bool
     */
    abstract public function save_content($formdata, $unilabel);

    /**
     * Get the formated intro text of the module instance.
     *
     * @param \stdClass $unilabel
     * @param \stdClass $cm
     * @return string
     */
    public function format_intro($unilabel, $cm) {
        return format_module_intro('unilabel', $unilabel, $cm->id, false);
    }

    /**
     * Get the localised plugin type name.
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', $this->get_namespace());
    }

    /**
     * Get the internal name of plugin type.
     *
     * @return string
     */
    public function get_plugintype() {
        return str_replace('unilabeltype_', '', $this->get_namespace());
    }

    /**
     * Validate all form values given in $data and returns an array with errors.
     * It does the same as the validation method in moodle forms.
     *
     * @param array $errors
     * @param array $data
     * @param array $files
     * @return array
     */
    public function form_validation($errors, $data, $files) {
        return $errors;
    }

    /** Get the configuration setting "active" to this plugin
     *
     * @return bool
     */
    abstract public function is_active();

    /**
     * Get the bootstrap definition for the col settings
     * It depends on the choosen count of columns in the settings
     *
     * @param int $columns
     * @return array
     */
    public function get_bootstrap_cols($columns) {
        /*
        count tiles lg    count tiles md    count tiles sm
        1 col-lg-12         1 col-md-12     1 col-sm-12
        2 col-lg-6          1 col-md-12     1 col-sm-12
        3 col-lg-4          2 col-md-6      1 col-sm-12
        4 col-lg-3          2 col-md-6      1 col-sm-12
        5 col-lg-2dot4      3 col-md-4      1 col-sm-12
        6 col-lg-2          3 col-md-4      1 col-sm-12
        */

        switch ($columns) {
            case 1:
                return ['colclasses' => 'col-12'];
            case 2:
                return ['colclasses' => 'col-lg-6 col-md-12'];
            case 3:
                return ['colclasses' => 'col-lg-4 col-md-6 col-sm-12'];
            case 4:
                return ['colclasses' => 'col-lg-3 col-md-6 col-sm-12'];
            case 5:
                return ['colclasses' => 'col-lg-2dot4 col-md-4 col-sm-12'];
            case 6:
                return ['colclasses' => 'col-lg-2 col-md-4 col-sm-12'];
            default:
                return ['colclasses' => 'col-lg-12 col-md-12 col-sm-12'];
        }
    }

}
