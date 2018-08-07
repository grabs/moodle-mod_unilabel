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

namespace unilabeltype_courseteaser;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    private $unilabeltyperecord;

    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $mform = $form->get_mform();
        $prefix = $this->get_namespace().'_';

        $mform->addElement('advcheckbox', $prefix.'showintro', get_string('showunilabeltext', $this->get_namespace()));

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', $this->get_namespace());

        $mform->addElement('course', $prefix.'courses', get_string('courses', $this->get_namespace()), array('multiple' => true));
        $mform->addRule($prefix.'courses', get_string('required'), 'required', null, 'client');

        $select = array(
            'carousel' => get_string('carousel', $this->get_namespace()),
            'grid' => get_string('grid', $this->get_namespace()),
        );

        $mform->addElement('select', $prefix.'presentation', get_string('presentation', $this->get_namespace()), $select);
    }

    public function get_form_default($data, $unilabel) {
        global $DB;
        $config = get_config($this->get_namespace());
        $prefix = $this->get_namespace().'_';

        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $data[$prefix.'presentation'] = $config->presentation;
            $data[$prefix.'showintro'] = $config->showintro;
        } else {
            $data[$prefix.'presentation'] = $unilabeltyperecord->presentation;
            $data[$prefix.'showintro'] = $unilabeltyperecord->showintro;
            $data[$prefix.'courses'] = explode(',', $unilabeltyperecord->courses);
        }

        return $data;
    }

    public function get_namespace() {
        return __NAMESPACE__;
    }

    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        $config = get_config($this->get_namespace());

        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $content = [
                'cmid' => $cm->id,
                'hasitems' => false,
            ];
            $template = 'default';
        } else {
            $intro = $this->format_intro($unilabel, $cm);
            $showintro = !empty($unilabeltyperecord->showintro);
            $items = $this->get_course_infos($unilabel);
            $content = [
                'showintro' => $showintro,
                'intro' => $showintro ? $intro : '',
                'interval' => $config->carouselinterval,
                'height' => 300,
                'items' => array_values($items),
                'hasitems' => count($items) > 0,
                'cmid' => $cm->id,
            ];
            switch ($unilabeltyperecord->presentation) {
                case 'carousel':
                    $template = 'carousel';
                    break;
                case 'grid':
                    $template = 'grid';
                    break;
                default:
                    $template = 'default';
            }
        }
        $content = $renderer->render_from_template($this->get_namespace().'/'.$template, $content);
        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB;

        $DB->delete_records($this->get_namespace(), array('unilabelid' => $unilabelid));
    }

    public function save_content($formdata, $unilabel) {
        global $DB;

        if (!$unilabletyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $unilabletyperecord = new \stdClass();
            $unilabletyperecord->unilabelid = $unilabel->id;
        }
        $prefix = $this->get_namespace().'_';

        $unilabletyperecord->presentation = $formdata->{$prefix.'presentation'};
        $unilabletyperecord->showintro = $formdata->{$prefix.'showintro'};
        $unilabletyperecord->courses = implode(',', $formdata->{$prefix.'courses'});

        if (empty($unilabletyperecord->id)) {
            $unilabletyperecord->id = $DB->insert_record($this->get_namespace(), $unilabletyperecord);
        } else {
            $DB->update_record($this->get_namespace(), $unilabletyperecord);
        }

        return !empty($unilabletyperecord->id);
    }

    private function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            $this->unilabeltyperecord = $DB->get_record($this->get_namespace(), array('unilabelid' => $unilabelid));
        }
        return $this->unilabeltyperecord;
    }

    public function get_course_infos($unilabel) {
        global $DB, $CFG;

        require_once($CFG->libdir.'/coursecatlib.php');

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);

        if (empty($unilabeltyperecord->courses)) {
            return array();
        }

        $courseids = explode(',', $unilabeltyperecord->courses);
        $items = array();
        $counter = 0;
        foreach ($courseids as $id) {
            if (!$course = $DB->get_record('course', array('id' => $id))) {
                continue;
            }
            $cil = new \course_in_list($course); // Special core object with some nice methods.
            $item = new \stdClass();

            $item->courseid = $course->id;
            $item->courseurl = new \moodle_url('/course/view.php', array('id' => $course->id));
            $item->title = $course->fullname;
            if ($cil->has_course_overviewfiles()) {
                $overviewfiles = $cil->get_course_overviewfiles();

                $file = array_shift($overviewfiles);

                // We have to build our own pluginfile url so we can control the output by our self.
                $imageurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $this->get_namespace(),
                    'overviewfiles',
                    $file->get_itemid(),
                    '/',
                    $file->get_filename()
                );
                $item->imageurl = $imageurl;
            }
            $item->nr = $counter;
            if ($counter == 0) {
                $item->first = true;
            }
            $counter++;
            $items[] = $item;
        }
        return $items;
    }
}
