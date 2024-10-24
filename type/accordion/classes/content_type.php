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

namespace unilabeltype_accordion;

/**
 * Class defining the accordion content type.
 *
 * @package     unilabeltype_accordion
 * @copyright   2022 Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_type extends \mod_unilabel\content_type {
    /** @var \stdClass */
    private $record;

    /** @var array */
    private $segments;

    /** @var \stdClass */
    private $cm;

    /** @var \context */
    private $context;

    /**
     * Get the namespace of this content type.
     *
     * @return string
     */
    public function get_namespace() {
        return __NAMESPACE__;
    }

    /**
     * Get true if the unilabeltype supports sortorder by using drag-and-drop.
     *
     * @return bool
     */
    public function use_sortorder() {
        return true;
    }

    /**
     * Load and cache the unilabel record.
     *
     * @param  int       $unilabelid
     * @return \stdClass
     */
    public function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->record)) {
            if (!$this->record = $DB->get_record('unilabeltype_accordion', ['unilabelid' => $unilabelid])) {
                $this->segments = [];

                return;
            }
            $this->cm      = get_coursemodule_from_instance('unilabel', $unilabelid);
            $this->context = \context_module::instance($this->cm->id);

            $this->segments = $DB->get_records('unilabeltype_accordion_seg', ['accordionid' => $this->record->id], 'sortorder ASC');
        }

        return $this->record;
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
        if (!$this->load_unilabeltype_record($unilabel->id)) {
            $content = [
                'intro'    => get_string('nocontent', $this->component),
                'cmid'     => $cm->id,
                'segments' => [],
            ];
        } else {
            $intro          = $this->format_intro($unilabel, $cm);
            $showintro      = !empty($this->record->showintro);
            $this->segments = array_values($this->segments);
            $this->segments = array_map(function ($v) {
                $v->heading = file_rewrite_pluginfile_urls(
                    $v->heading,
                    'pluginfile.php',
                    $this->context->id,
                    $this->component,
                    'heading',
                    $v->id
                );
                $v->content = file_rewrite_pluginfile_urls(
                    $v->content,
                    'pluginfile.php',
                    $this->context->id,
                    $this->component,
                    'content',
                    $v->id
                );

                return $v;
            }, $this->segments);
            $content = [
                'showintro' => $showintro,
                'intro'     => $showintro ? $intro : '',
                'segments'  => array_filter(array_values($this->segments), function ($v) {
                    return $v->heading != '' && $v->content != '';
                }),
                'cmid'     => $cm->id,
                'plugin'   => $this->component,
                'collapse' => $this->record->type == 1,
            ];
        }

        $accordion = $renderer->render_from_template('unilabeltype_accordion/accordion', $content);

        return $accordion;
    }

    /**
     * Delete the content of this type.
     *
     * @param  int  $unilabelid
     * @return void
     */
    public function delete_content($unilabelid) {
        global $DB;

        $this->load_unilabeltype_record($unilabelid);

        // Delete all segments.
        if (!empty($this->record)) {
            $DB->delete_records('unilabeltype_accordion_seg', ['accordionid' => $this->record->id]);
        }

        $DB->delete_records('unilabeltype_accordion', ['unilabelid' => $unilabelid]);
    }

    /**
     * Add elements to the activity settings form.
     *
     * @param  \mod_unilabel\edit_content_form $form
     * @param  \context                        $context
     * @return void
     */
    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        global $PAGE, $OUTPUT;
        $this->load_unilabeltype_record($form->unilabel->id);

        $mform  = $form->get_mform();
        $prefix = $this->component . '_';

        $mform->addElement('advcheckbox', $prefix . 'showintro', get_string('showunilabeltext', $this->component));

        $mform->addElement('advcheckbox', $prefix . 'type', get_string('collapse', $this->component));

        $textfieldoptions = [
            'subdirs'  => true,
            'context'  => $context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
        ];

        $formid       = $mform->getAttribute('id');
        $course       = $form->get_course();

        $repeatarray   = [];
        $repeatarray[] = $mform->createElement(
            'header',
            'singleelementheader',
            get_string('segment', $this->component) . '-{no}'
        );
        $repeatarray[] = $mform->createElement(
            'hidden',
            $prefix . 'sortorder'
        );
        $repeatarray[] = $mform->createElement(
            'editor',
            $prefix . 'heading',
            get_string('heading', $this->component),
            ['rows' => 2],
            $textfieldoptions
        );
        $repeatarray[] = $mform->createElement(
            'editor',
            $prefix . 'content',
            get_string('content', $this->component),
            ['rows' => 10],
            $textfieldoptions
        );

        $repeatedoptions                                = [];
        $repeatedoptions[$prefix . 'sortorder']['type'] = PARAM_INT;
        $repeatedoptions[$prefix . 'heading']['type']   = PARAM_RAW;
        $repeatedoptions[$prefix . 'content']['type']   = PARAM_RAW;
        // Adding the help buttons.
        $repeatedoptions[$prefix . 'heading']['helpbutton'] = ['heading', $this->component, '', true];
        $repeatedoptions[$prefix . 'content']['helpbutton'] = ['content', $this->component, '', true];

        $defaultrepeatcount = 1; // The default count for segments.
        $repeatcount        = count($this->segments);

        $form->repeat_elements(
            $repeatarray,
            $repeatcount,
            $repeatedoptions,
            'multiple_chosen_elements_count', // We need a fixed name here to get drag and drop work.
            $prefix . 'add_more_elements_btn', // This element musst be called so to get removed when dnd is enabled.
            $defaultrepeatcount,
            get_string('addmoresegments', $this->component),
            false
        );

        // This elements are needed by js to set empty hidden fields while deleting an element.
        $myelements = [
            'heading',
            'content',
        ];

        // Render the button to add elements.
        $btn = $OUTPUT->render_from_template('mod_unilabel/load_element_button', [
            'type' => $this->type,
            'formid' => $formid,
            'contextid' => $context->id,
            'courseid' => $course->id,
            'prefix' => $prefix,
        ]);
        $mform->addElement('html', $btn);
        // Add dynamic buttons like "Add item", "Delete" and "move".
        $PAGE->requires->js_call_amd(
            'mod_unilabel/add_dyn_formbuttons',
            'init',
            [
                $this->type,
                $formid,
                $context->id,
                $prefix,
                $myelements,
                $this->use_sortorder(), // Use drag and drop.
            ]
        );
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

        $cm      = get_coursemodule_from_instance('unilabel', $unilabel->id);
        $context = \context_module::instance($cm->id);

        $prefix = $this->component . '_';

        if (!$this->load_unilabeltype_record($unilabel->id)) {
            $data[$prefix . 'showintro'] = !empty($this->config->showintro);

            return $data;
        }
        $data[$prefix . 'showintro'] = $this->record->showintro;
        $data[$prefix . 'type']      = $this->record->type;

        if (!$segments = $DB->get_records(
            'unilabeltype_accordion_seg',
            ['accordionid' => $this->record->id],
            'sortorder ASC'
        )) {
            return $data;
        }

        $index = 0;
        foreach ($segments as $segment) {
            // Prepare the heading field.
            $elementname                  = $prefix . 'heading[' . $index . ']';
            $data[$elementname]['format'] = FORMAT_HTML;
            $draftid                      = file_get_submitted_draft_itemid($elementname);
            $data[$elementname]['text']   = file_prepare_draft_area(
                $draftid,
                $context->id,
                $this->component,
                'heading',
                $segment->id,
                null,
                $segment->heading
            );
            $data[$elementname]['itemid'] = $draftid;

            // Prepare the content field.
            $elementname                  = $prefix . 'content[' . $index . ']';
            $data[$elementname]['text']   = $segment->content;
            $data[$elementname]['format'] = FORMAT_HTML;
            $draftid                      = file_get_submitted_draft_itemid($elementname);
            $data[$elementname]['text']   = file_prepare_draft_area(
                $draftid,
                $context->id,
                $this->component,
                'content',
                $segment->id,
                null,
                $segment->content
            );
            $data[$elementname]['itemid'] = $draftid;

            // Prepare the sortorder field.
            $elementname        = $prefix . 'sortorder[' . $index . ']';
            $data[$elementname] = $segment->sortorder ?? ($index + 1);

            ++$index;
        }

        return $data;
    }

    /**
     * Save the content from settings page.
     *
     * @param  \stdClass $formdata
     * @param  \stdClass $unilabel
     * @return bool
     */
    public function save_content($formdata, $unilabel) {
        global $DB, $USER;

        $transaction = $DB->start_delegated_transaction();

        $prefix = $this->component . '_';

        // First save the accordion record.
        if (!$record = $DB->get_record('unilabeltype_accordion', ['unilabelid' => $unilabel->id])) {
            $record             = new \stdClass();
            $record->unilabelid = $unilabel->id;
            $record->id         = $DB->insert_record('unilabeltype_accordion', $record);
        }

        $record->showintro = $formdata->{$prefix . 'showintro'};
        $record->type      = $formdata->{$prefix . 'type'};

        $DB->update_record('unilabeltype_accordion', $record);

        $fs      = get_file_storage();
        $context = \context_module::instance($formdata->cmid);

        $fs->delete_area_files($context->id, $this->component, 'heading');
        $fs->delete_area_files($context->id, $this->component, 'content');

        $DB->delete_records('unilabeltype_accordion_seg', ['accordionid' => $record->id]);

        $potentialsegmentcount = $formdata->multiple_chosen_elements_count;
        for ($i = 0; $i < $potentialsegmentcount; ++$i) {
            if (empty($formdata->{$prefix . 'heading'}[$i]['text'])) {
                continue;
            }
            $heading = $formdata->{$prefix . 'heading'}[$i]['text'];
            $content = $formdata->{$prefix . 'content'}[$i]['text'];

            $segmentrecord              = new \stdClass();
            $segmentrecord->accordionid = $record->id;

            $segmentrecord->heading = file_rewrite_urls_to_pluginfile($heading, $formdata->{$prefix . 'heading'}[$i]['itemid']);
            $segmentrecord->content = file_rewrite_urls_to_pluginfile($content, $formdata->{$prefix . 'content'}[$i]['itemid']);

            $segmentrecord->sortorder = $formdata->{$prefix . 'sortorder'}[$i];

            $segmentrecord->id = $DB->insert_record('unilabeltype_accordion_seg', $segmentrecord);

            file_save_draft_area_files(
                $formdata->{$prefix . 'heading'}[$i]['itemid'],
                $context->id,
                $this->component,
                'heading',
                $segmentrecord->id,
                null
            );

            file_save_draft_area_files(
                $formdata->{$prefix . 'content'}[$i]['itemid'],
                $context->id,
                $this->component,
                'content',
                $segmentrecord->id,
                null
            );
        }

        $transaction->allow_commit();

        return !empty($record->id);
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
