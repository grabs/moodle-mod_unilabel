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

namespace unilabeltype_grid;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    private $unilabeltyperecord;
    private $tiles;
    private $cm;
    private $context;

    /**
     * Add elements to the activity settings form.
     *
     * @param \mod_unilabel\edit_content_form $form
     * @param \context $context
     * @return void
     */
    public function add_form_fragment(\mod_unilabel\edit_content_form $form, \context $context) {
        $unilabeltyperecord = $this->load_unilabeltype_record($form->unilabel->id);

        $mform = $form->get_mform();
        $prefix = $this->get_namespace().'_';

        $mform->addElement('advcheckbox', $prefix.'showintro', get_string('showunilabeltext', $this->get_namespace()));

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', $this->get_namespace());

        $numbers = array_combine(range(1, 6), range(1, 6));
        $mform->addElement('select', $prefix.'columns', get_string('columns', $this->get_namespace()), $numbers);

        $numbers = array_combine(range(100, 600, 50), range(100, 600, 50));
        $numbers = [0 => get_string('autoheight', $this->get_namespace())] + $numbers;
        $mform->addElement('select', $prefix.'height', get_string('height', $this->get_namespace()), $numbers);
        $mform->addHelpButton($prefix.'height', 'height', $this->get_namespace());

        $mform->addElement('checkbox', $prefix.'usemobile', get_string('use_mobile_images', $this->get_namespace()));
        $mform->addHelpButton($prefix.'usemobile', 'use_mobile_images', $this->get_namespace());

        $repeatarray = [];
        // If we want each repeated elment in a numbered group we add a header with '{no}' in its label.
        // This is replaced by the number of element.
        $repeatarray[] = $mform->createElement('header', $prefix.'tilehdr', get_string('tile', $this->get_namespace()).'-{no}');
        $repeatarray[] = $mform->createElement(
                                'text',
                                $prefix.'title',
                                get_string('title', $this->get_namespace()).'-{no}',
                                ['size' => 50]
        );
        $repeatarray[] = $mform->createElement(
                                'editor',
                                $prefix.'content',
                                get_string('content', $this->get_namespace()).'-{no}',
                                ['rows' => 10],
                                $this->editor_options($form->context)
        );
        $repeatarray[] = $mform->createElement(
                                'text',
                                $prefix.'url',
                                get_string('url', $this->get_namespace()).'-{no}',
                                ['size' => 50]
        );
        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix.'image',
            get_string('image', $this->get_namespace()).'-{no}',
            null,
            [
                'maxbytes' => $form->get_course()->maxbytes,
                'maxfiles' => 1,
                'subdirs' => false,
                'accepted_types' => ['web_image'],
            ]
        );
        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix.'image_mobile',
            get_string('image_mobile', $this->get_namespace()).'-{no}',
            null,
            [
                'maxbytes' => $form->get_course()->maxbytes,
                'maxfiles' => 1,
                'subdirs' => false,
                'accepted_types' => ['web_image'],
            ]
        );

        $repeatedoptions = [];
        $repeatedoptions[$prefix.'title']['type'] = PARAM_TEXT;
        $repeatedoptions[$prefix.'url']['type'] = PARAM_URL;
        $repeatedoptions[$prefix.'content']['type'] = PARAM_RAW;
        $repeatedoptions[$prefix.'image']['type'] = PARAM_FILE;
        $repeatedoptions[$prefix.'image_mobile']['type'] = PARAM_FILE;
        $repeatedoptions[$prefix.'image_mobile']['disabledif'] = [$prefix.'usemobile'];
        // Adding the help buttons.
        $repeatedoptions[$prefix.'content']['helpbutton'] = ['content', $this->get_namespace()];
        $repeatedoptions[$prefix.'url']['helpbutton'] = ['url', $this->get_namespace()];
        $repeatedoptions[$prefix.'image_mobile']['helpbutton'] = ['image_mobile', $this->get_namespace()];

        $defaultrepeatcount = 4; // The default count for tiles.
        $repeatcount = count($this->tiles);
        if ($rest = count($this->tiles) % $defaultrepeatcount) {
            $repeatcount = count($this->tiles) + ($defaultrepeatcount - $rest);
        }
        if ($repeatcount == 0) {
            $repeatcount = $defaultrepeatcount;
        }

        $nextel = $form->repeat_elements(
            $repeatarray,
            $repeatcount,
            $repeatedoptions,
            $prefix.'chosen_tiles_count',
            $prefix.'add_more_tiles_btn',
            $defaultrepeatcount, // Each time we add 3 elements.
            get_string('addmoretiles', $this->get_namespace()),
            false
        );
    }

    public function get_form_default($data, $unilabel) {
        global $DB;

        $cm = get_coursemodule_from_instance('unilabel', $unilabel->id);
        $context = \context_module::instance($cm->id);

        $prefix = $this->get_namespace().'_';

        // Set default data for the grid in generel.
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $config = get_config($this->get_namespace());
            $data[$prefix.'columns'] = $config->columns;
            $data[$prefix.'height'] = $config->height;
            $data[$prefix.'showintro'] = !empty($config->showintro);
            $data[$prefix.'usemobile'] = !empty($config->usemobile);
            return $data;
        }

        $data[$prefix.'columns'] = $unilabeltyperecord->columns;
        $data[$prefix.'height'] = $unilabeltyperecord->height;
        $data[$prefix.'showintro'] = $unilabeltyperecord->showintro;
        $data[$prefix.'usemobile'] = $unilabeltyperecord->usemobile;

        // Set default data for tiles.
        if (!$tiles = $DB->get_records(
            $this->get_namespace().'_tile',
                                            ['gridid' => $unilabeltyperecord->id],
            'id ASC'
        )) {
            return $data;
        }

        $index = 0;
        foreach ($tiles as $tile) {
            // Prepare the title field.
            $elementname = $prefix.'title['.$index.']';
            $data[$elementname] = $tile->title;

            // Prepare the url field.
            $elementname = $prefix.'url['.$index.']';
            $data[$elementname] = $tile->url;

            // Prepare the content field.
            $elementname = $prefix.'content['.$index.']';
            $draftitemidcontent = 0;
            $data[$elementname]['text'] =
                                file_prepare_draft_area($draftitemidcontent,
                                $context->id,
                                $this->get_namespace(),
                                'content',
                                $tile->id,
                                array('subdirs' => true),
                                $tile->content);

            $data[$elementname]['format'] = FORMAT_HTML;
            $data[$elementname]['itemid'] = $draftitemidcontent;

            // Prepare the images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemidimage = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemidimage, $context->id, $this->get_namespace(), 'image', $tile->id);
            $elementname = $prefix.'image['.$index.']';
            $data[$elementname] = $draftitemidimage;

            // Prepare the mobile images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemidimagemobile = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemidimagemobile, $context->id, $this->get_namespace(), 'image_mobile', $tile->id);
            $elementname = $prefix.'image_mobile['.$index.']';
            $data[$elementname] = $draftitemidimagemobile;
            $index++;
        }

        return $data;
    }

    public function get_namespace() {
        return __NAMESPACE__;
    }

    public function get_content($unilabel, $cm, \plugin_renderer_base $renderer) {
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $content = [
                'intro' => get_string('nocontent', $this->get_namespace()),
                'cmid' => $cm->id,
                'hastiles' => false,
            ];
        } else {
            $intro = $this->format_intro($unilabel, $cm);
            $showintro = !empty($unilabeltyperecord->showintro);
            $content = [
                'showintro' => $showintro,
                'intro' => $showintro ? $intro : '',
                'columnssmall' => 1,
                'height' => $unilabeltyperecord->height,
                'autoheight' => empty($unilabeltyperecord->height),
                'tiles' => array_values($this->tiles),
                'hastiles' => count($this->tiles) > 0,
                'cmid' => $cm->id,
                // If columns = 5 we need extra css because bootstrap does not support this.
                'extracss' => ($unilabeltyperecord->columns == 5),
            ];
            $content += $this->get_bootstrap_cols($unilabeltyperecord->columns);
        }
        $content = $renderer->render_from_template($this->get_namespace().'/grid', $content);

        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB;

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabelid);

        // Delete all tiles.
        if (!empty($unilabeltyperecord)) {
            $DB->delete_records($this->get_namespace().'_tile', ['gridid' => $unilabeltyperecord->id]);
        }

        $DB->delete_records($this->get_namespace(), ['unilabelid' => $unilabelid]);
    }

    public function save_content($formdata, $unilabel) {
        global $DB, $USER;

        // We want to keep the tiles consistent so we start a transaction here.
        $transaction = $DB->start_delegated_transaction();

        $prefix = $this->get_namespace().'_';

        // First save the grid record.
        if (!$unilabeltyperecord = $DB->get_record($this->get_namespace(), ['unilabelid' => $unilabel->id])) {
            $unilabeltyperecord = new \stdClass();
            $unilabeltyperecord->unilabelid = $unilabel->id;
            $unilabeltyperecord->id = $DB->insert_record($this->get_namespace(), $unilabeltyperecord);
        }

        $unilabeltyperecord->columns = $formdata->{$prefix.'columns'};
        $unilabeltyperecord->height = $formdata->{$prefix.'height'};
        $unilabeltyperecord->showintro = $formdata->{$prefix.'showintro'};
        $unilabeltyperecord->usemobile = !empty($formdata->{$prefix.'usemobile'});

        $DB->update_record($this->get_namespace(), $unilabeltyperecord);

        $fs = get_file_storage();
        $context = \context_module::instance($formdata->cmid);
        $usercontext = \context_user::instance($USER->id);

        // First: remove old tile images.
        // We use the module_context as context and this component as component.
        $fs->delete_area_files($context->id, $this->get_namespace(), 'image');
        $fs->delete_area_files($context->id, $this->get_namespace(), 'image_mobile');
        $fs->delete_area_files($context->id, $this->get_namespace(), 'content');

        // Second: remove old tile records.
        $DB->delete_records($this->get_namespace().'_tile', ['gridid' => $unilabeltyperecord->id]);

        // How many tiles could be defined (we have an array here)?
        // They may not all used so some could be left out.
        $potentialtilecount = $formdata->{$prefix.'chosen_tiles_count'};
        for ($i = 0; $i < $potentialtilecount; $i++) {
            // Get the draftitemids to identify the submitted files in image, imagemobile and content.
            $draftitemid = $formdata->{$prefix.'image'}[$i];
            if (!empty($unilabeltyperecord->usemobile)) {
                $draftitemidmobile = $formdata->{$prefix.'image_mobile'}[$i];
            }
            $draftitemidcontent = $formdata->{$prefix.'content'}[$i]['itemid'];

            // Do we have an image? We get this information with file_get_draft_area_info().
            $fileinfo = file_get_draft_area_info($draftitemid);
            // We only create a record if we have at least a title, a file or a content.
            $title = $formdata->{$prefix.'title'}[$i];
            $content = $formdata->{$prefix.'content'}[$i]['text'];
            if (empty($title) and $fileinfo['filecount'] < 1 and !$this->html_has_content($content)) {
                continue;
            }

            $tilerecord = new \stdClass();
            $tilerecord->gridid = $unilabeltyperecord->id;
            $tilerecord->title = $title;
            $tilerecord->url = $formdata->{$prefix.'url'}[$i];

            $tilerecord->content = ''; // Dummy content.
            $tilerecord->id = $DB->insert_record($this->get_namespace().'_tile', $tilerecord);

            // Save draft files from content and convert the pluginfile links.
            $tilerecord->content = file_save_draft_area_files($draftitemidcontent,
                        $context->id,
                        $this->get_namespace(),
                        'content',
                        $tilerecord->id,
                        $this->editor_options($context),
                        $content);
            $DB->update_record($this->get_namespace().'_tile', $tilerecord);

            // Now we can save our draft files for image and imagemobile.
            file_save_draft_area_files($draftitemid, $context->id, $this->get_namespace(), 'image', $tilerecord->id);
            if (!empty($formdata->{$prefix.'usemobile'})) {
                file_save_draft_area_files(
                    $draftitemidmobile,
                            $context->id,
                            $this->get_namespace(),
                            'image_mobile',
                            $tilerecord->id
                );
            }
        }

        $transaction->allow_commit();

        return !empty($unilabeltyperecord->id);
    }

    public function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            if (!$this->unilabeltyperecord = $DB->get_record($this->get_namespace(), ['unilabelid' => $unilabelid])) {
                $this->tiles = [];
                return;
            }
            $this->cm = get_coursemodule_from_instance('unilabel', $unilabelid);
            $this->context = \context_module::instance($this->cm->id);

            $tiles = $DB->get_records($this->get_namespace().'_tile', ['gridid' => $this->unilabeltyperecord->id]);
            $index = 0;

            foreach ($tiles as $tile) {
                $tile->imageurl = $this->get_image_for_tile($tile);
                $tile->imagemobileurl = $this->get_image_mobile_for_tile($tile);
                $tile->title = empty($tile->title) ? get_string('tilenr', $this->get_namespace(), $index + 1) : $tile->title;
                $tile->content = $this->format_content($tile, $this->context);
                $tile->nr = $index;
                $index++;
            }
            $this->tiles = $tiles;
        }
        return $this->unilabeltyperecord;
    }

    private function get_image_for_tile($tile) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->context->id, $this->get_namespace(), 'image', $tile->id, '', $includedirs = false);
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url(
            $this->context->id,
            $this->get_namespace(),
            'image',
            $tile->id,
            '/',
            $file->get_filename()
        );
        return $imageurl;
    }

    private function get_image_mobile_for_tile($tile) {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->context->id,
                                    $this->get_namespace(),
                                    'image_mobile',
                                    $tile->id,
                                    '',
                                    $includedirs = false
        );
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url(
            $this->context->id,
            $this->get_namespace(),
            'image_mobile',
            $tile->id,
            '/',
            $file->get_filename()
        );
        return $imageurl;
    }

    private function html_has_content($content) {
        $searches = [
            '<br>',
            '<br />',
            '<p>',
            '</p>'
        ];

        $check = trim(str_replace($searches, '', $content));

        return !empty($check);
    }

    private function get_bootstrap_cols($columns) {
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
                return ['colclasses' => 'col-12'];
            case 2:
                return ['colclasses' => 'col-lg-6 col-md-12'];
            case 3:
                return ['colclasses' => 'col-lg-4 col-md-6 col-sm-12'];
            case 4:
                return ['colclasses' => 'col-lg-3 col-md-6 col-sm-12'];
            case 5:
                return ['colclasses' => 'col-lg-2dot4 col-md-4 col-sm-12'];
            case 6:
                return ['colclasses' => 'col-lg-2 col-md-4 col-sm-12'];
            default:
                return ['colclasses' => 'col-lg-12 col-md-12 col-sm-12'];
        }
    }

    public function editor_options($context) {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true,
            'context' => $context,
            'subdirs' => true
        ];
    }

    public function format_options($context) {
        return [
            'noclean' => true,
            'context' => $context
        ];
    }

    public function format_content($tile, $context) {
        global $CFG;
        require_once($CFG->libdir.'/filelib.php');

        $options = $this->format_options($context);
        $content = file_rewrite_pluginfile_urls(
                $tile->content,
                'pluginfile.php',
                $context->id,
                $this->get_namespace(),
                'content',
                $tile->id
        );

        return trim(format_text($content, FORMAT_HTML, $options, null));
    }

}
