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
 * unilabel type collapsedtext.
 *
 * @package     unilabeltype_collapsedtext
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_collapsedtext;

/**
 * Content type definition.
 * @package     unilabeltype_collapsedtext
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_type extends \mod_unilabel\content_type {
    /** The presentation type "collapsed" which show a bootstrap collapsed element. */
    public const PRESENTATION_COLLAPSED = 'collapsed';
    /** The presentation type "dialog" which shows a bootstrap modalbox. */
    public const PRESENTATION_DIALOG = 'dialog';

    /** @var \stdClass */
    private $unilabeltyperecord;

    /**
     * Add elements to the activity settings form.
     *
     * @param  \mod_unilabel\edit_content_form $form
     * @param  \context                        $context
     * @return void
     */
    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $mform  = $form->get_mform();
        $prefix = 'unilabeltype_collapsedtext_';

        $mform->addElement('header', $prefix . 'hdr', $this->get_name());
        $mform->addHelpButton($prefix . 'hdr', 'pluginname', 'unilabeltype_collapsedtext');

        $mform->addElement('text', $prefix . 'title', get_string('title', 'unilabeltype_collapsedtext'), ['size' => 40]);
        $mform->setType($prefix . 'title', PARAM_TEXT);
        $mform->addRule($prefix . 'title', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', $prefix . 'applytextfilters', get_string('applytextfilters', 'unilabeltype_collapsedtext'));
        $mform->addHelpButton($prefix . 'applytextfilters', 'applytextfilters', 'unilabeltype_collapsedtext');

        $select = [
            static::PRESENTATION_COLLAPSED => get_string('collapsed', 'unilabeltype_collapsedtext'),
            static::PRESENTATION_DIALOG    => get_string('dialog', 'unilabeltype_collapsedtext'),
        ];
        $mform->addElement('select', $prefix . 'presentation', get_string('presentation', 'unilabeltype_collapsedtext'), $select);

        $mform->addElement('checkbox', $prefix . 'useanimation', get_string('useanimation', 'unilabeltype_collapsedtext'));
    }

    /**
     * Get the default values for the settings form.
     *
     * @param  array     $data
     * @param  \stdClass $unilabel
     * @return array
     */
    public function get_form_default($data, $unilabel) {
        global $DB;

        $this->load_unilabeltype_record($unilabel->id);
        $prefix = 'unilabeltype_collapsedtext_';

        $data[$prefix . 'title']            = $unilabel->name;
        $data[$prefix . 'applytextfilters'] = static::get_applytextfilters();
        $data[$prefix . 'useanimation']     = static::get_useanimation();
        $data[$prefix . 'presentation']     = static::get_presentation();

        return $data;
    }

    /**
     * Get the namespace of this content type.
     *
     * @return string
     */
    public function get_namespace() {
        return __NAMESPACE__;
    }

    /**
     * Get the html formated content for this type.
     *
     * @param  \stdClass             $unilabel
     * @param  \stdClass             $cm
     * @param  \plugin_renderer_base $renderer
     * @return string
     */
    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        $this->load_unilabeltype_record($unilabel->id);
        $cmidfromurl = optional_param('cmid', 0, PARAM_INT);
        $intro        = $this->format_intro($unilabel, $cm);
        $useanimation = $this->get_useanimation();
        $applytextfilters = $this->get_applytextfilters();

        $title = $this->get_title($unilabel);
        if ($applytextfilters) {
            $title = format_text($title, FORMAT_HTML, ['noclean' => true]);
        }

        $content = [
            'title'            => $title,
            'applytextfilters' => $applytextfilters,
            'content'          => $intro,
            'cmid'             => $cm->id,
            'useanimation'     => $useanimation,
            'srtitle_expand'   => get_string('expand'),
            'srtitle_collapse' => get_string('collapse'),
        ];

        if ($cm->id == $cmidfromurl) {
            $content['openonstart'] = true;
        }

        $presentation = static::get_presentation();

        switch ($presentation) {
            case static::PRESENTATION_COLLAPSED:
                $template = 'collapsed';
                break;
            case static::PRESENTATION_DIALOG:
                $template = 'dialog';
                break;
            default:
                throw new \moodle_exception('Wrong presentation type: '. $presentation);
        }

        $content = $renderer->render_from_template('unilabeltype_collapsedtext/' . $template, $content);

        return $content;
    }

    /**
     * Delete the content of this type.
     *
     * @param  int  $unilabelid
     * @return void
     */
    public function delete_content($unilabelid) {
        global $DB;

        $DB->delete_records('unilabeltype_collapsedtext', ['unilabelid' => $unilabelid]);
    }

    /**
     * Save the content from settings page.
     *
     * @param  \stdClass $formdata
     * @param  \stdClass $unilabel
     * @return bool
     */
    public function save_content($formdata, $unilabel) {
        global $DB;
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $unilabeltyperecord             = new \stdClass();
            $unilabeltyperecord->unilabelid = $unilabel->id;
        }

        $prefix = 'unilabeltype_collapsedtext_';

        $title        = $formdata->{$prefix . 'title'};

        $unilabeltyperecord->applytextfilters = !empty($formdata->{$prefix . 'applytextfilters'});
        $unilabeltyperecord->useanimation = !empty($formdata->{$prefix . 'useanimation'});
        $unilabeltyperecord->presentation = $formdata->{$prefix . 'presentation'};

        if (empty($unilabeltyperecord->id)) {
            $unilabeltyperecord->id = $DB->insert_record('unilabeltype_collapsedtext', $unilabeltyperecord);
        } else {
            $DB->update_record('unilabeltype_collapsedtext', $unilabeltyperecord);
        }
        $DB->set_field('unilabel', 'name', $title, ['id' => $unilabel->id]);

        return !empty($unilabeltyperecord->id);
    }

    /**
     * Get the title which is the clickable link.
     *
     * @param  \stdClass $unilabel
     * @return string
     */
    public function get_title($unilabel) {
        return $unilabel->name;
    }

    /**
     * Get the sort of presentation.
     *
     * @return string On of the constants static::PRESENTATION_COLLAPSED or static::PRESENTATION_DIALOG
     */
    public function get_presentation() {
        return $this->unilabeltyperecord->presentation ?? $this->config->presentation;
    }

    /**
     * Do we want to apply text filters.
     *
     * @return bool
     */
    public function get_applytextfilters() {
        return (bool) ($this->unilabeltyperecord->applytextfilters ?? $this->config->applytextfilters);
    }

    /**
     * Do we want animation or not.
     *
     * @return bool
     */
    public function get_useanimation() {
        return (bool) ($this->unilabeltyperecord->useanimation ?? $this->config->useanimation);
    }

    /**
     * Load and cache the unilabel record.
     *
     * @param  int       $unilabelid
     * @return \stdClass
     */
    private function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            $this->unilabeltyperecord = $DB->get_record('unilabeltype_collapsedtext', ['unilabelid' => $unilabelid]);
        }

        return $this->unilabeltyperecord;
    }

    /**
     * Check that this plugin is activated on config settings.
     *
     * @return bool
     */
    public function is_active() {
        return !empty($this->config->active);
    }
}
