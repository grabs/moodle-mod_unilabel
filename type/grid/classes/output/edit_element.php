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
 * unilabel type grid.
 *
 * @package     unilabeltype_grid
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_grid\output;

/**
 * Content type definition.
 * @package     unilabeltype_grid
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_element implements \templatable, \renderable {
    protected $data;

    protected $course;
    protected $formid;
    protected $context;
    protected $prefix;
    protected $repeatindex;
    /** @var \core_renderer */
    protected $output;

    public function __construct(string $formid, \context $context, \stdClass $course, string $prefix, int $repeatindex) {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . '/formslib.php');
        require_once($CFG->libdir . '/form/filemanager.php');
        require_once($CFG->libdir . '/form/editor.php');
        require_once($CFG->libdir . '/form/text.php');
        require_once($CFG->libdir . '/form/hidden.php');
        require_once($CFG->libdir . '/form/header.php');
        require_once($CFG->libdir . '/form/static.php');

        $this->output = $OUTPUT;

        $this->data = new \stdClass();

        $this->formid = $formid;
        $this->context = $context;
        $this->course = $course;
        $this->prefix = $prefix;
        $this->repeatindex = $repeatindex;

        // $picker       = new \mod_unilabel\output\component\activity_picker($course, $formid);
        $inputidbase  = 'id_' . $prefix . 'url_';
        $pickerbutton = new \mod_unilabel\output\component\activity_picker_button($formid, $inputidbase);

        $this->data->formid = $formid;
        $this->data->repeatindex = $repeatindex;
        $this->data->prefix = $prefix;
        $this->data->repeatnr = $repeatindex + 1;
        $this->data->titleelement = $this->get_textfield('title');
        $this->data->urlelement = $this->get_textfield('url');
        $this->data->pickerbutton = $this->get_static($OUTPUT->render($pickerbutton));
        $this->data->contentelement = $this->get_editor('content');
        $this->data->imageelement = $this->get_filemanager('image');
        $this->data->imagemobileelement = $this->get_filemanager('image_mobile');
        $this->data->sortorderelement = $this->get_hidden('sortorder');

    }

    /**
     * Export for template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }

    /**
     * Get the options array to support files in editor.
     *
     * @return array
     */
    public function editor_options() {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean'  => true,
            'context'  => $this->context,
            'subdirs'  => true,
        ];
    }

    public function manager_options() {
        return [
            'maxbytes'       => $this->course->maxbytes,
            'maxfiles'       => 1,
            'subdirs'        => false,
            'accepted_types' => ['web_image'],
        ];
    }

    protected function get_filemanager(string $name) {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        $label = get_string($name, 'unilabeltype_grid') . '-' . ($this->repeatindex + 1);

        $element = new \MoodleQuickForm_filemanager($elementname, $label, $attributes, $this->manager_options());
        // return $element->toHtml();
        return $this->output->mform_element($element, false, false, '', false);
    }

    protected function get_editor(string $name) {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        $label = get_string($name, 'unilabeltype_grid') . '-' . ($this->repeatindex + 1);

        $element = new \MoodleQuickForm_editor($elementname, $label, $attributes, $this->editor_options());
        // return $element->toHtml();
        return $this->output->mform_element($element, false, false, '', false);
    }

    protected function get_textfield(string $name) {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;
        $attributes['size'] = 50;

        $label = get_string($name, 'unilabeltype_grid') . '-' . ($this->repeatindex + 1);

        $element = new \MoodleQuickForm_text($elementname, $label, $attributes);
        // return $element->toHtml();
        return $this->output->mform_element($element, false, false, '', false);
    }

    protected function get_hidden(string $name) {

        $elementname = $this->prefix . $name . '[' . $this->repeatindex . ']';
        $attributes = [];
        $attributes['name'] = $elementname;

        $element = new \MoodleQuickForm_hidden($elementname, $this->repeatindex, $attributes);
        // return $this->output->mform_element($element, false, false, '', false);
        return $element->toHtml();
    }

    protected function get_static($html) {
        $name = 'picker';
        $elementname = $this->prefix . $name . '_' . $this->repeatindex;
        $attributes = [];
        $attributes['id'] = 'id_' . $this->prefix . $name . '_' . $this->repeatindex;
        $attributes['name'] = $elementname;

        $element = new \MoodleQuickForm_static($elementname, '', $html);
        $element->setAttributes($attributes);
        // return $element->toHtml();
        return $this->output->mform_element($element, false, false, '', false);

    }
}
