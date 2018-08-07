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
        $prefix = $this->get_namespace().'_';

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', $this->get_namespace());

        $mform->addElement('text', $prefix.'title', get_string('title', $this->get_namespace()), array('size' => 40));
        $mform->setType($prefix.'title', PARAM_TEXT);
        $mform->addRule($prefix.'title', get_string('required'), 'required', null, 'client');

        $select = array(
            'collapsed' => get_string('collapsed', $this->get_namespace()),
            'dialog' => get_string('dialog', $this->get_namespace()),
        );
        $mform->addElement('select', $prefix.'presentation', get_string('presentation', $this->get_namespace()), $select);

        $mform->addElement('checkbox', $prefix.'useanimation', get_string('useanimation', $this->get_namespace()));

    }

    public function get_form_default($data, $unilabel) {
        global $DB; /** @var \moodle_database $DB */
        $config = get_config($this->get_namespace());
        $prefix = $this->get_namespace().'_';

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

        $content = $renderer->render_from_template($this->get_namespace().'/'.$template, $content);

        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB; /** @var \moodle_database $DB */

        $DB->delete_records($this->get_namespace(), array('unilabelid' => $unilabelid));
    }

    public function save_content($formdata, $unilabel) {
        global $DB; /** @var \moodle_database $DB */
        if (!$unilabletyperecord = $this->load_unilabeltype_record($unilabel)) {
            $unilabletyperecord = new \stdClass();
            $unilabletyperecord->unilabelid = $unilabel->id;
        }

        $prefix = $this->get_namespace().'_';

        $unilabletyperecord->title = $formdata->{$prefix.'title'};
        $unilabletyperecord->useanimation = !empty($formdata->{$prefix.'useanimation'});
        $unilabletyperecord->presentation = $formdata->{$prefix.'presentation'};

        if (empty($unilabletyperecord->id)) {
            $unilabletyperecord->id = $DB->insert_record($this->get_namespace(), $unilabletyperecord);
        } else {
            $DB->update_record($this->get_namespace(), $unilabletyperecord);
        }

        return !empty($unilabletyperecord->id);
    }

    public function get_title($unilabel) {
        $this->load_unilabeltype_record($unilabel);

        if (empty($this->unilabeltyperecord->title)) {
            return get_string('notitle', $this->get_namespace());
        }
        return $this->unilabeltyperecord->title;
    }

    public function get_useanimation($unilabel) {
        if (empty($this->unilabeltyperecord)) {
            $config = get_config($this->get_namespace());
            return $config->useanimation;
        }
        $this->load_unilabeltype_record($unilabel);

        return !empty($this->unilabeltyperecord->useanimation);
    }

    private function load_unilabeltype_record($unilabel) {
        global $DB; /** @var \moodle_database $DB */

        if (empty($this->unilabeltyperecord)) {
            $this->unilabeltyperecord = $DB->get_record($this->get_namespace(), array('unilabelid' => $unilabel->id));
        }
        return $this->unilabeltyperecord;
    }
}
