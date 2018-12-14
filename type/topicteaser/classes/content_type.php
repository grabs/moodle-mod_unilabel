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

namespace unilabeltype_topicteaser;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    private $unilabeltyperecord;

    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $mform = $form->get_mform();
        $prefix = 'unilabeltype_topicteaser_';

        $mform->addElement('advcheckbox', $prefix.'showintro', get_string('showunilabeltext', 'unilabeltype_topicteaser'));

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', 'unilabeltype_topicteaser');

        $mform->addElement('advcheckbox', $prefix.'showcoursetitle', get_string('showcoursetitle', 'unilabeltype_topicteaser'));

        $mform->addElement('course', $prefix.'course', get_string('course'), array('multiple' => false));

        $select = array(
            'carousel' => get_string('carousel', 'unilabeltype_topicteaser'),
            'grid' => get_string('grid', 'unilabeltype_topicteaser'),
        );
        $mform->addElement('select', $prefix.'presentation', get_string('presentation', 'unilabeltype_topicteaser'), $select);

        $select = array(
            'opendialog' => get_string('opendialog', 'unilabeltype_topicteaser'),
            'opencourseurl' => get_string('opencourseurl', 'unilabeltype_topicteaser'),
        );
        $mform->addElement('select', $prefix.'clickaction', get_string('clickaction', 'unilabeltype_topicteaser'), $select);
    }

    public function get_form_default($data, $unilabel) {
        global $DB;
        $config = get_config('unilabeltype_topicteaser');
        $prefix = 'unilabeltype_topicteaser_';

        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $data[$prefix.'presentation'] = $config->presentation;
            $data[$prefix.'clickaction'] = $config->clickaction;
            $data[$prefix.'showintro'] = $config->showintro;
            $data[$prefix.'showcoursetitle'] = $config->showcoursetitle;
        } else {
            $data[$prefix.'presentation'] = $unilabeltyperecord->presentation;
            $data[$prefix.'clickaction'] = $unilabeltyperecord->clickaction;
            $data[$prefix.'showintro'] = $unilabeltyperecord->showintro;
            $data[$prefix.'showcoursetitle'] = $unilabeltyperecord->showcoursetitle;
            $data[$prefix.'course'] = $unilabeltyperecord->course;
        }

        return $data;
    }

    public function get_namespace() {
        return __NAMESPACE__;
    }

    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        global $DB;

        $config = get_config('unilabeltype_topicteaser');

        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $content = [
                'cmid' => $cm->id,
                'hasitems' => false,
            ];
            $template = 'default';
        } else {
            $intro = $this->format_intro($unilabel, $cm);
            $showintro = !empty($unilabeltyperecord->showintro);
            $courseid = empty($unilabeltyperecord->course) ? $unilabel->course : $unilabeltyperecord->course;
            $items = $this->get_sections_html($courseid);
            $title = null;
            if (!empty($unilabeltyperecord->showcoursetitle)) {
                if ($course = $DB->get_record('course', array('id' => $courseid))) {
                    $title = $course->fullname;
                } else {
                    $title = get_string('coursenotfound', 'unilabeltype_topicteaser');
                }
            }
            $content = [
                'title' => $title,
                'showintro' => $showintro,
                'intro' => $showintro ? $intro : '',
                'interval' => $config->carouselinterval,
                'height' => 300,
                'items' => array_values($items),
                'hasitems' => count($items) > 0,
                'openmodal' => ($unilabeltyperecord->clickaction == 'opendialog'),
                'opencourseurl' => ($unilabeltyperecord->clickaction == 'opencourseurl'),
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
        $content = $renderer->render_from_template('unilabeltype_topicteaser/'.$template, $content);
        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB; /** @var \moodle_database $DB */

        $DB->delete_records('unilabeltype_topicteaser', array('unilabelid' => $unilabelid));
    }

    public function save_content($formdata, $unilabel) {
        global $DB;

        if (!$unilabletyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $unilabletyperecord = new \stdClass();
            $unilabletyperecord->unilabelid = $unilabel->id;
        }
        $prefix = 'unilabeltype_topicteaser_';

        $unilabletyperecord->presentation = $formdata->{$prefix.'presentation'};
        $unilabletyperecord->clickaction = $formdata->{$prefix.'clickaction'};
        $unilabletyperecord->showintro = $formdata->{$prefix.'showintro'};
        $unilabletyperecord->showcoursetitle = $formdata->{$prefix.'showcoursetitle'};
        $unilabletyperecord->course = $formdata->{$prefix.'course'};

        if (empty($unilabletyperecord->id)) {
            $unilabletyperecord->id = $DB->insert_record('unilabeltype_topicteaser', $unilabletyperecord);
        } else {
            $DB->update_record('unilabeltype_topicteaser', $unilabletyperecord);
        }

        return !empty($unilabletyperecord->id);
    }

    private function load_unilabeltype_record($unilabelid) {
        global $DB;

        $config = get_config('unilabeltype_topicteaser');

        if (empty($this->unilabeltyperecord)) {
            $this->unilabeltyperecord = $DB->get_record('unilabeltype_topicteaser', array('unilabelid' => $unilabelid));
            if (empty($this->unilabeltyperecord->clickaction)) {
                $this->unilabeltyperecord->clickaction = $config->clickaction;
            }
        }
        return $this->unilabeltyperecord;
    }

    public function get_sections_from_course($courseid) {
        global $DB;

        $params = array('course' => $courseid, 'visible' => 1);
        if (!$sectionsrecords = $DB->get_records('course_sections', $params, 'section')) {
            return array();
        }

        $return = array();
        foreach ($sectionsrecords as $s) {
            if ($s->section == 0) {
                continue;
            }
            $urlparams = array('id' => $s->course);
            $sectionanchor = 'section-'.$s->section;
            $s->url = new \moodle_url('/course/view.php', $urlparams, $sectionanchor);
            $return[] = $s;
        }
        return $return;
    }
    public function get_sections_html($courseid) {
        global $DB, $PAGE;

        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            return array();
        }
        $sections = $this->get_sections_from_course($courseid);
        $courseformat = course_get_format($course->id);

        $sectionsoutput = array();
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $counter = 0;
        foreach ($sections as $s) {
            $section = new \stdClass();
            $section->name = get_section_name($course, $s);
            $section->section = $s->section;
            $section->viewurl = $courseformat->get_view_url($s->section);

            $context = \context_course::instance($s->course);
            $summarytext = file_rewrite_pluginfile_urls($s->summary, 'pluginfile.php',
                                                        $context->id,
                                                        'unilabeltype_topicteaser',
                                                        'section', $s->id);

            $options = new \stdClass();
            $options->noclean = true;
            $options->overflowdiv = true;

            $section->summary = format_text($summarytext, $s->summaryformat, $options);

            $section->cmlist = $courserenderer->course_section_cm_list($course, $s->section);
            $section->nr = $counter;

            if ($counter == 0) {
                $section->first = true;
            }

            $sectionsoutput[] = $section;
            $counter++;

        }

        return $sectionsoutput;
    }
}
