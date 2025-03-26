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
 * mod_unilabel
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel\output;

/**
 * Content type definition.
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class edit_element_base implements \templatable, \renderable {

    /** @var \stdClass */
    protected $data;

    /** @var \stdClass */
    protected $course;
    /** @var string */
    protected $formid;
    /** @var string */
    protected $context;
    /** @var string */
    protected $type;
    /** @var string */
    protected $prefix;
    /** @var string */
    protected $component;
    /** @var int */
    protected $repeatindex;
    /** @var \plugin_renderer_base */
    protected $output;

    /**
     * Constructor
     *
     * @param string $formid The id the edit_content form (mform) is using
     * @param \context $context The context of the cm
     * @param \stdClass $course
     * @param string $type The unilabel type like "grid" or "carousel"
     * @param int $repeatindex
     */
    public function __construct(string $formid, \context $context, \stdClass $course, string $type, int $repeatindex) {
        global $CFG, $OUTPUT;

        require_once($CFG->libdir . '/formslib.php');
        require_once($CFG->libdir . '/form/filemanager.php');
        require_once($CFG->libdir . '/form/editor.php');
        require_once($CFG->libdir . '/form/text.php');
        require_once($CFG->libdir . '/form/hidden.php');
        require_once($CFG->libdir . '/form/header.php');
        require_once($CFG->libdir . '/form/static.php');
        require_once($CFG->libdir . '/form/group.php');
        require_once($CFG->libdir . '/form/select.php');
        require_once($CFG->libdir . '/form/checkbox.php');

        // Set the global properties.
        $this->output = $OUTPUT;
        $this->formid = $formid;
        $this->context = $context;
        $this->course = $course;
        $this->type = $type;
        $this->component = 'unilabeltype_' . $type;
        $this->prefix = $this->component . '_';
        $this->repeatindex = $repeatindex;

        // Set the common values for the output array.
        $this->data = new \stdClass();
        $this->data->formid = $this->formid;
        $this->data->type = $this->type;
        $this->data->repeatindex = $this->repeatindex;
        $this->data->prefix = $this->prefix;
        $this->data->repeatnr = $this->repeatindex + 1;
    }

    /**
     * Get the name of the elements group.
     *
     * @return string
     */
    abstract public function get_elements_name();

    /**
     * Get the form elements as array in the order they should be printed out.
     *
     * @return \HTML_QuickForm_element[]
     */
    abstract public function get_elements();

    /**
     * Add a visibility element to the form fragment.
     *
     * @return void
     */
    protected function add_visibility() {
        $visibleelement = $this->get_hidden('visible');
        $visibleelement->setValue(1);
        $this->data->visibilityelement = $this->render_element(
            $visibleelement
        );
    }

    /**
     * Add a sortorder element to the form fragment.
     *
     * @return void
     */
    protected function add_sortorder() {
        $this->data->sortorderelement = $this->render_element(
            $this->get_hidden('sortorder')
        );
    }

    /**
     * Export for template.
     *
     * @param \renderer_base $output The renderer.
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $elements = $this->get_elements();
        $this->data->elements = [];
        foreach ($elements as $element) {
            $this->data->elements[] = $this->render_element($element);
        }
        $this->data->elementsname = $this->get_elements_name();
        return $this->data;
    }

    /**
     * Get the rendered html from the given QuickForm element.
     *
     * @param \HTML_QuickForm_element $element
     * @return void
     */
    protected function render_element(\HTML_QuickForm_element $element) {
        if ($element->getType() == 'hidden') {
            return $element->toHtml();
        }

        return $this->output->mform_element(
            $element,
            false,
            false,
            '',
            false
        );
    }

    /**
     * Get an mform filemanager element.
     *
     * @param string $name The element name without the prefix.
     * @param array $attributes
     * @param array $options The options for file handling
     * @param string $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_filemanager The element
     */
    protected function get_filemanager(string $name, array $attributes = [],
                                        array $options = [], $helpbutton = '', string $extralabel = '') {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_filemanager($elementname, $label, $attributes, $options);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }

        return $element;
    }

    /**
     * Get an mform editor element.
     *
     * @param string $name The element name without the prefix.
     * @param array $attributes
     * @param array $options The options for file handling
     * @param boolean $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_editor The element
     */
    protected function get_editor(string $name, array $attributes = [],
                                            array $options = [], $helpbutton = '', string $extralabel = '') {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_editor($elementname, $label, $attributes, $options);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }

        return $element;
    }

    /**
     * Get an mform text element.
     *
     * @param string $name The element name without the prefix.
     * @param array $attributes
     * @param boolean $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_text The element
     */
    protected function get_textfield(string $name, array $attributes = [], $helpbutton = '', string $extralabel = '') {
        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_text($elementname, $label, $attributes);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }

        return $element;
    }

    /**
     * Get an mform checkbox element.
     *
     * @param string $name The element name without the prefix.
     * @param array $attributes
     * @param boolean $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_text The element
     */
    protected function get_checkbox(string $name, array $attributes = [], $helpbutton = '', string $extralabel = '') {
        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_checkbox($elementname, $label, '', $attributes);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }

        return $element;
    }

    /**
     * Get an mform select element.
     *
     * @param string $name The element name without the prefix.
     * @param array $options
     * @param array $attributes
     * @param boolean $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_text The element
     */
    protected function get_select(string $name, array $options, array $attributes = [], $helpbutton = '', string $extralabel = '') {
        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_select($elementname, $label, $options, $attributes);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }

        return $element;
    }

    /**
     * Get an mform hidden element.
     *
     * @param string $name The element name without the prefix.
     * @return \MoodleQuickForm_hidden The element
     */
    protected function get_hidden(string $name) {
        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes = [];
        $attributes['name'] = $elementname;

        $element = new \MoodleQuickForm_hidden($elementname, $this->repeatindex, $attributes);

        return $element;
    }

    /**
     * Get an mform static element.
     *
     * @param string $name The element name without the prefix.
     * @param string $html
     * @return \MoodleQuickForm_static The element
     */
    protected function get_static(string $name, string $html) {
        $elementname = $this->prefix . $name . '_' . $this->repeatindex;
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        $element = new \MoodleQuickForm_static($elementname, '', $html);
        $element->setAttributes($attributes);

        return $element;

    }

    /**
     * Get an mform group element.
     *
     * @param string $name The element name without the prefix.
     * @param \HTML_QuickForm_element[] $elements
     * @param string $helpbutton
     * @param string $extralabel In from the name independent label.
     * @return \MoodleQuickForm_group The group element
     */
    protected function get_group(string $name, array $elements, $helpbutton = '', string $extralabel = '') {

        $elementname = $this->prefix . $name . '_' . $this->repeatindex;
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        if (empty($extralabel)) {
            $label = get_string($name, $this->component) . '-' . ($this->repeatindex + 1);
        } else {
            $label = $extralabel;
        }

        $element = new \MoodleQuickForm_group($elementname, $label, $elements, null, false);
        if ($helpbutton) {
            $element->_helpbutton = $this->output->help_icon($helpbutton, $this->component);
        }
        $element->setAttributes($attributes); // The group element needs at least this attributes!!!

        return $element;
    }
}
