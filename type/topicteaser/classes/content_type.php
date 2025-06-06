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
 * unilabel type topic teaser.
 *
 * @package     unilabeltype_topicteaser
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_topicteaser;

use mod_unilabel\setting_configselect_button;

/**
 * Content type definition.
 * @package     unilabeltype_topicteaser
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_type extends \mod_unilabel\content_type {
    /** @var \stdClass */
    private $unilabeltyperecord;

    /**
     * Get the content of a section. This method is used by the fragment api to load this content by ajax.
     *
     * @param  int    $courseid
     * @param  int    $sectionnumber
     * @return string
     */
    public static function get_sections_content($courseid, $sectionnumber) {
        global $DB, $PAGE;

        if (!$course = $DB->get_record('course', ['id' => $courseid])) {
            return '';
        }

        /** @var \core_course_renderer $courserenderer */
        $courserenderer = $PAGE->get_renderer('core', 'course');
        $courseformat   = course_get_format($course->id);

        $modinfo     = $courseformat->get_modinfo();
        $sectioninfo = $modinfo->get_section_info($sectionnumber);
        $cmlist      = new \core_courseformat\output\local\content\section\cmlist($courseformat, $sectioninfo);
        $output      = $courserenderer->render_from_template(
            'core_courseformat/local/content/section/cmlist',
            $cmlist->export_for_template($courserenderer)
        );

        return $output;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        if (empty($this->config->columns)) {
            $this->config->columns = 4;
        }
    }

    /**
     * Add elements to the activity settings form.
     *
     * @param  \mod_unilabel\edit_content_form $form
     * @param  \context                        $context
     * @return void
     */
    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $mform  = $form->get_mform();
        $prefix = 'unilabeltype_topicteaser_';

        $mform->addElement('advcheckbox', $prefix . 'showintro', get_string('showunilabeltext', 'unilabeltype_topicteaser'));

        $mform->addElement('header', $prefix . 'hdr', $this->get_name());
        $mform->addHelpButton($prefix . 'hdr', 'pluginname', 'unilabeltype_topicteaser');

        $mform->addElement('advcheckbox', $prefix . 'showcoursetitle', get_string('showcoursetitle', 'unilabeltype_topicteaser'));

        $courseoptions = [
            'multiple'             => false,
            'limittoenrolled'      => !is_siteadmin(),
            'requiredcapabilities' => [
                    'moodle/course:manageactivities',
            ],
        ];
        $mform->addElement('course', $prefix . 'course', get_string('course'), $courseoptions);

        $select = [
            'carousel' => get_string('carousel', 'unilabeltype_topicteaser'),
            'grid'     => get_string('grid', 'unilabeltype_topicteaser'),
        ];
        $mform->addElement('select', $prefix . 'presentation', get_string('presentation', 'unilabeltype_topicteaser'), $select);

        $numbers = array_combine(range(1, 6), range(1, 6));
        $mform->addElement('select', $prefix . 'columns', get_string('columns', 'unilabeltype_topicteaser'), $numbers);
        $mform->disabledIf($prefix . 'columns', $prefix . 'presentation', 'ne', 'grid');

        // In all smaller displays we can not use 5 columns. It is not supported by bootstrap and css injection will not work here.
        $numbers       = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 6 => 6];
        $strdefaultcol = get_string('default_columns', 'unilabeltype_topicteaser');
        $columnsmiddle = $mform->createElement('select', $prefix . 'columnsmiddle', '', $numbers);
        $defaultmiddle = $mform->createElement('advcheckbox', $prefix . 'defaultmiddle', $strdefaultcol);
        $mform->addGroup(
            [
                $columnsmiddle,
                $defaultmiddle,
            ],
            $prefix . 'group_middle',
            get_string('columnsmiddle', 'unilabeltype_topicteaser'),
            [' '],
            false
        );
        $mform->disabledIf($prefix . 'columnsmiddle', $prefix . 'defaultmiddle', 'checked');
        $mform->disabledIf($prefix . 'group_middle', $prefix . 'presentation', 'ne', 'grid');

        $columnssmall = $mform->createElement('select', $prefix . 'columnssmall', '', $numbers);
        $defaultsmall = $mform->createElement('advcheckbox', $prefix . 'defaultsmall', $strdefaultcol);
        $mform->addGroup(
            [
                $columnssmall,
                $defaultsmall,
            ],
            $prefix . 'group_small',
            get_string('columnssmall', 'unilabeltype_topicteaser'),
            [' '],
            false
        );
        $mform->disabledIf($prefix . 'columnssmall', $prefix . 'defaultsmall', 'checked');
        $mform->disabledIf($prefix . 'group_small', $prefix . 'presentation', 'ne', 'grid');

        $mform->addElement(
            'checkbox',
            $prefix . 'autorun',
            get_string('autorun', 'mod_unilabel'),
            ''
        );
        $autorundefault = !empty($this->config->autorun);
        $mform->setDefault($prefix . 'autorun', $autorundefault);
        $mform->disabledIf($prefix . 'autorun', $prefix . 'presentation', 'ne', 'carousel');

        $numbers = array_combine(range(1, 10), range(1, 10));
        $mform->addElement(
            'select',
            $prefix . 'carouselinterval',
            get_string('carouselinterval', 'unilabeltype_topicteaser'),
            $numbers
        );
        $mform->disabledIf($prefix . 'carouselinterval', $prefix . 'presentation', 'ne', 'carousel');
        $mform->hideIf($prefix . 'carouselinterval', $prefix . 'autorun', 'notchecked');

        $select = [
            'opendialog'    => get_string('opendialog', 'unilabeltype_topicteaser'),
            'opencourseurl' => get_string('opencourseurl', 'unilabeltype_topicteaser'),
        ];
        $mform->addElement('select', $prefix . 'clickaction', get_string('clickaction', 'unilabeltype_topicteaser'), $select);
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
        $prefix = 'unilabeltype_topicteaser_';

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);

        $data[$prefix . 'presentation'] = $unilabeltyperecord->presentation ?? $this->config->presentation;
        $data[$prefix . 'columns']      = $unilabeltyperecord->columns ?? $this->config->columns;
        if (empty($columnsmiddle = $this->get_coloumnsmiddle($unilabel))) {
            $columns = $this->get_default_col_middle($unilabeltyperecord->columns ?? $this->config->columns);
            $data[$prefix . 'columnsmiddle'] = $columns;
            $data[$prefix . 'defaultmiddle'] = true;
        } else {
            $data[$prefix . 'columnsmiddle'] = $columnsmiddle;
            $data[$prefix . 'defaultmiddle'] = false;
        }
        if (empty($columnssmall = $this->get_coloumnssmall($unilabel))) {
            $data[$prefix . 'columnssmall'] = $this->get_default_col_small();
            $data[$prefix . 'defaultsmall'] = true;
        } else {
            $data[$prefix . 'columnssmall'] = $columnssmall;
            $data[$prefix . 'defaultsmall'] = false;
        }

        $data[$prefix . 'carouselinterval'] = $unilabeltyperecord->carouselinterval ?? $this->config->carouselinterval;

        $autorun = empty($unilabeltyperecord) ? $this->config->autorun : !empty($unilabeltyperecord->carouselinterval);
        $data[$prefix . 'autorun']          = $autorun;

        $data[$prefix . 'clickaction']      = $this->get_click_action($unilabel);
        $data[$prefix . 'showintro']        = $unilabeltyperecord->showintro ?? $this->config->showintro;
        $data[$prefix . 'showcoursetitle']  = $unilabeltyperecord->showcoursetitle ?? $this->config->showcoursetitle;

        $data[$prefix . 'course']           = $this->get_selected_courseid($unilabel);

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
        global $DB;

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);

        $intro     = $this->format_intro($unilabel, $cm);
        $showintro = !empty($unilabeltyperecord->showintro);
        $courseid  = $this->get_selected_courseid($unilabel);
        $items     = $this->get_sections_html($courseid, $unilabel);
        $title     = null;
        if (!empty($unilabeltyperecord->showcoursetitle)) {
            if ($course = $DB->get_record('course', ['id' => $courseid])) {
                $title = $course->fullname;
            } else {
                $title = get_string('coursenotfound', 'unilabeltype_topicteaser');
            }
        }
        $content = [
            'title'         => $title,
            'showintro'     => $showintro,
            'intro'         => $showintro ? $intro : '',
            'interval'      => $unilabeltyperecord->carouselinterval ?? $this->config->carouselinterval,
            'items'         => array_values($items),
            'hasitems'      => count($items) > 0,
            'openmodal'     => ($this->get_click_action($unilabel) == 'opendialog'),
            'opencourseurl' => ($this->get_click_action($unilabel) == 'opencourseurl'),
            'cmid'          => $cm->id,
            'plugin'        => 'unilabeltype_topicteaser',
        ];
        $presentation = $unilabeltyperecord->presentation ?? $this->config->presentation;
        switch ($presentation) {
            case 'carousel':
                $template = 'carousel';
                $template = 'carousel';
                if (!empty($this->config->custombutton)) {
                    $fontbuttons = setting_configselect_button::get_font_buttons();
                    $content['custombuttons']   = 1;
                    $content['fontawesomenext'] = $fontbuttons[$this->config->custombutton]['next'];
                    $content['fontawesomeprev'] = $fontbuttons[$this->config->custombutton]['prev'];

                    // To make sure we have clean html we have to put the carousel css into the <head> by using javascript.
                    $cssstring                = $renderer->render_from_template('mod_unilabel/carousel_button_style', $content);
                    $content['cssjsonstring'] = json_encode($cssstring);
                }
                break;
            case 'grid':
                $template              = 'grid';
                $content['colclasses'] = $this->get_bootstrap_cols(
                    $unilabeltyperecord->columns ?? $this->config->columns,
                    $this->get_coloumnsmiddle($unilabel),
                    $this->get_coloumnssmall($unilabel)
                );
                break;
            default:
                $template = 'default';
        }

        $content = $renderer->render_from_template('unilabeltype_topicteaser/' . $template, $content);

        return $content;
    }

    /**
     * Delete the content of this type.
     *
     * @param  int  $unilabelid
     * @return void
     */
    public function delete_content($unilabelid) {
        global $DB; /** @var \moodle_database $DB */
        $DB->delete_records('unilabeltype_topicteaser', ['unilabelid' => $unilabelid]);
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
        $prefix = 'unilabeltype_topicteaser_';

        $unilabeltyperecord->presentation = $formdata->{$prefix . 'presentation'};
        $columns                          = !empty($formdata->{$prefix . 'columns'}) ? $formdata->{$prefix . 'columns'} : 0;
        $unilabeltyperecord->columns      = $columns;
        $columnsmiddle                    = !empty($formdata->{$prefix . 'defaultmiddle'})
                                                ? null : $formdata->{$prefix . 'columnsmiddle'};
        $unilabeltyperecord->columnsmiddle = $columnsmiddle;
        $columnssmall                      = !empty($formdata->{$prefix . 'defaultsmall'})
                                                ? null : $formdata->{$prefix . 'columnssmall'};
        $unilabeltyperecord->columnssmall = $columnssmall;

        if (!empty($formdata->{$prefix . 'autorun'})) {
            $unilabeltyperecord->carouselinterval = $formdata->{$prefix . 'carouselinterval'};
        } else {
            $unilabeltyperecord->carouselinterval = 0;
        }
        $unilabeltyperecord->clickaction     = $formdata->{$prefix . 'clickaction'};
        $unilabeltyperecord->showintro       = $formdata->{$prefix . 'showintro'};
        $unilabeltyperecord->showcoursetitle = $formdata->{$prefix . 'showcoursetitle'};
        $course                              = 0;
        if (is_numeric($formdata->{$prefix . 'course'})) {
            $course = (int) $formdata->{$prefix . 'course'};
        }
        $unilabeltyperecord->course = $course;

        if (empty($unilabeltyperecord->id)) {
            $unilabeltyperecord->id = $DB->insert_record('unilabeltype_topicteaser', $unilabeltyperecord);
        } else {
            $DB->update_record('unilabeltype_topicteaser', $unilabeltyperecord);
        }

        return !empty($unilabeltyperecord->id);
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
            $this->unilabeltyperecord = $DB->get_record('unilabeltype_topicteaser', ['unilabelid' => $unilabelid]);
        }

        return $this->unilabeltyperecord;
    }

    /**
     * Get the click action for the unilabel.
     *
     * This function retrieves the click action setting from the unilabeltype_topicteaser table
     * for the given unilabel. If the record does not exist or the click action is not set,
     * it returns the default click action from the plugin configuration.
     *
     * @param \stdClass $unilabel The unilabel object for which to get the click action.
     * @return string The click action for the unilabel.
     */
    public function get_click_action($unilabel) {
        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);
        return $unilabeltyperecord->clickaction ?? $this->config->clickaction;
    }

    /**
     * Retrieves the middle column count for the unilabel.
     *
     * If the middle column count is not set in the unilabeltype_topicteaser table,
     * it returns the default middle column count based on the column count.
     *
     * @param \stdClass $unilabel The unilabel object for which to get the middle column count.
     * @return int The middle column count for the unilabel.
     */
    public function get_coloumnsmiddle($unilabel) {
        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);
        if (empty($unilabeltyperecord->columnsmiddle)) {
            return $this->get_default_col_middle($unilabeltyperecord->columns ?? $this->config->columns);
        } else {
            return $unilabeltyperecord->columnsmiddle;
        }
    }

    /**
     * Retrieves the small column count for the unilabel.
     *
     * If the small column count is not set in the unilabeltype_topicteaser table,
     * it returns the default small column count.
     *
     * @param \stdClass $unilabel The unilabel object for which to get the small column count.
     * @return int The small column count for the unilabel.
     */
    public function get_coloumnssmall($unilabel) {
        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);
        if (empty($unilabeltyperecord->columnssmall)) {
            return $this->get_default_col_small();
        } else {
            return $unilabeltyperecord->columnssmall;
        }
    }

    /**
     * Retrieves the course ID associated with this unilabel type.
     *
     * This function retrieves the course ID from the unilabeltype_topicteaser table
     * for the given unilabel. If the course ID is not set,
     * it returns the course ID from the unilabel object.
     *
     * @param \stdClass $unilabel The unilabel object for which to get the course ID.
     * @return int The course ID associated with the unilabel.
     */
    public function get_selected_courseid($unilabel) {
        $unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id);
        return $unilabeltyperecord->course ?? $unilabel->course;
    }

    /**
     * Get the sections from the given course.
     *
     * @param  int   $courseid
     * @return array
     */
    public function get_sections_from_course($courseid) {
        global $DB;

        $params = ['courseid' => $courseid, 'visible' => 1];
        $select = 'course = :courseid AND
                   visible = :visible AND
                   component IS NULL';
        if (!$sectionsrecords = $DB->get_records_select('course_sections', $select, $params, 'section')) {
            return [];
        }

        $return = [];
        foreach ($sectionsrecords as $s) {
            if ($s->section == 0) {
                continue;
            }
            $urlparams     = ['id' => $s->course];
            $sectionanchor = 'section-' . $s->section;
            $s->url        = new \moodle_url('/course/view.php', $urlparams, $sectionanchor);
            $return[]      = $s;
        }

        return $return;
    }

    /**
     * Get the html formated content of all sections.
     *
     * @param  int      $courseid
     * @param \stdClass $unilabel
     * @return array
     */
    public function get_sections_html($courseid, $unilabel) {
        global $DB, $PAGE;

        $mycm = get_coursemodule_from_instance('unilabel', $unilabel->id);
        if ($PAGE->course->id == $courseid) {
            $mysection = $DB->get_record('course_sections', ['id' => $mycm->section]);
        }

        if (!$course = $DB->get_record('course', ['id' => $courseid])) {
            return [];
        }

        /** @var \core_course_renderer $courserenderer */
        $courserenderer = $PAGE->get_renderer('core', 'course');

        $sections     = $this->get_sections_from_course($courseid);
        $courseformat = course_get_format($course->id);
        $modinfo      = $courseformat->get_modinfo();

        $sectionsoutput = [];
        $counter        = 0;
        foreach ($sections as $s) {
            if (!empty($mysection)) {
                if ($mysection->id == $s->id) {
                    continue;
                }
            }
            $section          = new \stdClass();
            $section->name    = get_section_name($course, $s);
            $section->section = $s->section;
            $section->viewurl = $courseformat->get_view_url($s->section);

            $context     = \context_course::instance($s->course);
            $summarytext = file_rewrite_pluginfile_urls($s->summary, 'pluginfile.php',
                $context->id,
                'unilabeltype_topicteaser',
                'section', $s->id);

            $options              = new \stdClass();
            $options->noclean     = true;
            $options->overflowdiv = true;

            $section->summary   = format_text($summarytext, $s->summaryformat, $options);
            $section->contextid = $context->id;

            $section->nr = $counter;

            if ($counter == 0) {
                $section->first = true;
            }

            $sectionsoutput[] = $section;

            // Prepare js for this section.
            $serviceparams            = new \stdClass();
            $serviceparams->sectionid = $s->id;
            $contentcontainerselector = '#section-content-' . $mycm->id . '-' . $counter;
            $fragmentcall             = 'section';
            $contextid                = $context->id;
            $triggerselector          = '#topicteaser-modal-' . $mycm->id . '-' . $counter;
            $triggerevent             = 'show.bs.modal';
            $amdparams                = [
                $contentcontainerselector,
                $fragmentcall,
                $serviceparams,
                $contextid,
                $triggerselector,
                $triggerevent,
            ];
            $PAGE->requires->js_call_amd('unilabeltype_topicteaser/content_loader', 'init', $amdparams);

            ++$counter;
        }

        return $sectionsoutput;
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
