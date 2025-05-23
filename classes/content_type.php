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
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel;

/**
 * General class to define a content type.
 *
 * This class is used in all sub plugins "unilabeltype".
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class content_type {
    /** @var \stdClass */
    protected $config;

    /** @var string */
    protected $type;

    /** @var string */
    protected $component;

    /** @var string The prefix, which is used in the mform */
    protected $prefix;

    /**
     * Constructor to initialize the type.
     */
    public function __construct() {
        $this->init_type($this->get_namespace());
    }

    /**
     * This method must be called in each constructor.
     *
     * @param string $component // The component of the unilabeltype e.g. "unilabeltype_grid".
     * @return void
     */
    public function init_type(string $component) {
        $this->component = $component;
        $this->type = str_replace('unilabeltype_', '', $component);
        $this->prefix = $component . '_';
        $this->config = get_config($this->component);
    }

    /**
     * Get the component as string (e.g. "unilabeltype_grid")..
     *
     * @return string
     */
    public function get_component() {
        return $this->component;
    }

    /**
     * Get the an information to the user hwo to edit this type after creation.
     *
     * @return string
     */
    public function get_edit_info(): string {
        return get_string('edit_info', $this->get_namespace());
    }

    /**
     * Get the type as string (e.g. "grid").
     *
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get the prefix as string (e.g. "unilabeltype_grid_").
     *
     * @return string
     */
    public function get_prefix() {
        return $this->prefix;
    }

    /**
     * Get the config of the unilabeltype.
     *
     * @return \stdClass
     */
    public function get_config() {
        return $this->config;
    }

    /**
     * Get true if the unilabeltype supports sortorder by using drag-and-drop.
     *
     * @return bool
     */
    public function use_sortorder() {
        return false;
    }

    /**
     * Get true if the unilabeltype supports the visibility button.
     *
     * @return bool
     */
    public function use_visibility() {
        return false;
    }

    /**
     * Get the namespace of the content type class.
     *
     * @return string
     */
    abstract public function get_namespace();

    /**
     * Get the html output of the content type.
     *
     * @param  \stdClass             $unilabel
     * @param  \stdClass             $cm
     * @param  \plugin_renderer_base $renderer
     * @return string
     */
    abstract public function get_content($unilabel, $cm, \plugin_renderer_base $renderer);

    /**
     * Delete the content from database.
     *
     * @param  int  $unilabelid
     * @return void
     */
    abstract public function delete_content($unilabelid);

    /**
     * Add form elements needed by the content type class.
     *
     * @param  edit_content_form $form
     * @param  \context          $context
     * @return void
     */
    abstract public function add_form_fragment(edit_content_form $form, \context $context);

    /**
     * Get all default values for the content type used in the settings form.
     *
     * @param  array     $data
     * @param  \stdClass $unilabel
     * @return array
     */
    abstract public function get_form_default($data, $unilabel);

    /**
     * Save the content into the database.
     *
     * @param  \stdClass $formdata
     * @param  \stdClass $unilabel
     * @return bool
     */
    abstract public function save_content($formdata, $unilabel);

    /**
     * Get the formated intro text of the module instance.
     *
     * @param  \stdClass $unilabel
     * @param  \stdClass $cm
     * @return string
     */
    public function format_intro($unilabel, $cm) {
        return format_module_intro('unilabel', $unilabel, $cm->id, true);
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
     * @param  array $errors
     * @param  array $data
     * @param  array $files
     * @return array
     */
    public function form_validation($errors, $data, $files) {
        return $errors;
    }

    /** Get the configuration setting "active" to this plugin.
     *
     * @return bool
     */
    abstract public function is_active();

    /**
     * Get the bootstrap definition for the col settings
     * It depends on the choosen count of columns in the settings
     * The result is the 'colclasses' array with the string 'col-lg-xy col-md-xy col-sm-xy'.
     *
     * @param  int   $columns
     * @param  int   $columnsmiddle
     * @param  int   $columnssmall
     * @return array
     */
    public function get_bootstrap_cols($columns, $columnsmiddle = null, $columnssmall = null) {
        $columnslg = $this->get_bootstrap_col($columns, 'lg');

        if (empty($columnsmiddle)) {
            $columnsmd = $this->get_bootstrap_col($this->get_default_col_middle($columns), 'md');
        } else {
            $columnsmd = $this->get_bootstrap_col($columnsmiddle, 'md');
        }

        if (empty($columnssmall)) {
            $columnssm = $this->get_bootstrap_col($this->get_default_col_small(), 'sm');
        } else {
            $columnssm = $this->get_bootstrap_col($columnssmall, 'sm');
        }

        $colstrings   = [];
        $colstrings[] = $columnslg;
        $colstrings[] = $columnsmd;
        $colstrings[] = $columnssm;

        return implode(' ', $colstrings);
    }

    /**
     * Get the bootstrap grid col class depending on the number of columns.
     *
     * @param  int    $columns
     * @param  string $breakpoint
     * @return string
     */
    public function get_bootstrap_col($columns, $breakpoint) {
        switch ($columns) {
            case 1:
                return 'col-' . $breakpoint . '-12';
            case 2:
                return 'col-' . $breakpoint . '-6';
            case 3:
                return 'col-' . $breakpoint . '-4';
            case 4:
                return 'col-' . $breakpoint . '-3';
            case 5:
                return 'col-' . $breakpoint . '-2dot4';
            case 6:
                return 'col-' . $breakpoint . '-2';
            default:
                return 'col-' . $breakpoint . '-12';
        }
    }

    /**
     * Get the default col value depending on the amount of columns.
     *
     * @param  int $columns
     * @return int
     */
    public function get_default_col_middle($columns) {
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
            case 2:
                return 1;
            case 3:
            case 4:
                return 2;
            case 5:
            case 6:
                return 3;
            default:
                return 1;
        }
    }

    /**
     * Get the default col value for small screens. This can be overriden.
     *
     * @return int
     */
    public function get_default_col_small() {
        return 1;
    }

    /**
     * Add a colourpicker element into the settings form.
     *
     * @param  \MoodleQuickForm $mform
     * @param  string           $name
     * @param  string           $label
     * @param  string           $defaultvalue
     * @return void
     */
    public function add_colourpicker($mform, $name, $label, $defaultvalue) {
        global $PAGE;
        $renderer                           = $PAGE->get_renderer('mod_unilabel');
        $colourpickercontent                = new \stdClass();
        $colourpickercontent->iconurl       = $renderer->image_url('i/colourpicker');
        $colourpickercontent->inputname     = $name . '_hidden';
        $colourpickercontent->inputid       = 'id_' . $name . '_colourpicker';
        $colourpickercontent->hiddeninputid = 'id_' . $name . '_colourpicker_hidden';
        $colourpickercontent->label         = $label;
        $colourpickercontent->defaultvalue  = $defaultvalue;
        $colourpickerhtml                   = $renderer->render_from_template('mod_unilabel/colourpicker', $colourpickercontent);
        $mform->addElement('html', $colourpickerhtml);
        $mform->addElement('text', $name, '', ['id' => $colourpickercontent->inputid]);
        $mform->setType($name, PARAM_TEXT);
        $PAGE->requires->js_init_call('M.util.init_colour_picker', [$colourpickercontent->hiddeninputid, null]);
    }
}
