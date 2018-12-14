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

namespace unilabeltype_collapsedtext;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    private $unilabeltyperecord;

    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $mform = $form->get_mform();
        $prefix = 'unilabeltype_collapsedtext_';

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', 'unilabeltype_collapsedtext');

        $mform->addElement('text', $prefix.'title', get_string('title', 'unilabeltype_collapsedtext'), array('size' => 40));
        $mform->setType($prefix.'title', PARAM_TEXT);
        $mform->addRule($prefix.'title', get_string('required'), 'required', null, 'client');

        $select = array(
            'collapsed' => get_string('collapsed', 'unilabeltype_collapsedtext'),
            'dialog' => get_string('dialog', 'unilabeltype_collapsedtext'),
        );
        $mform->addElement('select', $prefix.'presentation', get_string('presentation', 'unilabeltype_collapsedtext'), $select);

        $mform->addElement('checkbox', $prefix.'useanimation', get_string('useanimation', 'unilabeltype_collapsedtext'));

    }

    public function get_form_default($data, $unilabel) {
        global $DB;
        $config = get_config('unilabeltype_collapsedtext');
        $prefix = 'unilabeltype_collapsedtext_';

        if (!$unilabletyperecord = $this->load_unilabeltype_record($unilabel)) {
            $data[$prefix.'title'] = '';
            $data[$prefix.'useanimation'] = $config->useanimation;
            $data[$prefix.'presentation'] = $config->presentation;
        } else {
            $data[$prefix.'title'] = $unilabletyperecord->title;
            $data[$prefix.'useanimation'] = $unilabletyperecord->useanimation;
            $data[$prefix.'presentation'] = $unilabletyperecord->presentation;
        }
        return $data;
    }

    public function get_namespace() {
        return __NAMESPACE__;
    }

    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        $cmidfromurl = optional_param('cmid', 0, PARAM_INT);
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel)) {
            $content = array();
            $template = 'default';
        } else {
            $intro = $this->format_intro($unilabel, $cm);
            $useanimation = $this->get_useanimation($unilabel);

            $content = [
                'title' => $this->get_title($unilabel),
                'content' => $intro,
                'cmid' => $cm->id,
                'useanimation' => $useanimation,
            ];

            if ($cm->id == $cmidfromurl) {
                $content['openonstart'] = true;
            }

            switch ($unilabeltyperecord->presentation) {
                case 'collapsed':
                    $template = 'collapsed';
                    break;
                case 'dialog':
                    $template = 'dialog';
                    break;
                default:
                    $template = 'default';
            }
        }

        $content = $renderer->render_from_template('unilabeltype_collapsedtext/'.$template, $content);

        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB;

        $DB->delete_records('unilabeltype_collapsedtext', array('unilabelid' => $unilabelid));
    }

    public function save_content($formdata, $unilabel) {
        global $DB;
        if (!$unilabletyperecord = $this->load_unilabeltype_record($unilabel)) {
            $unilabletyperecord = new \stdClass();
            $unilabletyperecord->unilabelid = $unilabel->id;
        }

        $prefix = 'unilabeltype_collapsedtext_';

        $unilabletyperecord->title = $formdata->{$prefix.'title'};
        $unilabletyperecord->useanimation = !empty($formdata->{$prefix.'useanimation'});
        $unilabletyperecord->presentation = $formdata->{$prefix.'presentation'};

        if (empty($unilabletyperecord->id)) {
            $unilabletyperecord->id = $DB->insert_record('unilabeltype_collapsedtext', $unilabletyperecord);
        } else {
            $DB->update_record('unilabeltype_collapsedtext', $unilabletyperecord);
        }

        return !empty($unilabletyperecord->id);
    }

    public function get_title($unilabel) {
        $this->load_unilabeltype_record($unilabel);

        if (empty($this->unilabeltyperecord->title)) {
            return get_string('notitle', 'unilabeltype_collapsedtext');
        }
        return $this->unilabeltyperecord->title;
    }

    public function get_useanimation($unilabel) {
        if (empty($this->unilabeltyperecord)) {
            $config = get_config('unilabeltype_collapsedtext');
            return $config->useanimation;
        }
        $this->load_unilabeltype_record($unilabel);

        return !empty($this->unilabeltyperecord->useanimation);
    }

    private function load_unilabeltype_record($unilabel) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            $this->unilabeltyperecord = $DB->get_record('unilabeltype_collapsedtext', array('unilabelid' => $unilabel->id));
        }
        return $this->unilabeltyperecord;
    }
}
