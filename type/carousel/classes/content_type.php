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

namespace unilabeltype_carousel;

defined('MOODLE_INTERNAL') || die;

class content_type extends \mod_unilabel\content_type {
    private $unilabeltyperecord;
    private $slides;
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
        $prefix = 'unilabeltype_carousel_';

        $mform->addElement('advcheckbox', $prefix.'showintro', get_string('showunilabeltext', 'unilabeltype_carousel'));

        $mform->addElement('header', $prefix.'hdr', $this->get_name());
        $mform->addHelpButton($prefix.'hdr', 'pluginname', 'unilabeltype_carousel');

        $numbers = array_combine(range(1, 10), range(1, 10));
        $mform->addElement('select', $prefix.'carouselinterval', get_string('carouselinterval', 'unilabeltype_carousel'), $numbers);

        $numbers = array_combine(range(100, 600, 50), range(100, 600, 50));
        $numbers = array(0 => get_string('autoheight', 'unilabeltype_carousel')) + $numbers;
        $mform->addElement('select', $prefix.'height', get_string('height', 'unilabeltype_carousel'), $numbers);
        $mform->addHelpButton($prefix.'height', 'height', 'unilabeltype_carousel');

        $backgrounddefault = empty($unilabeltyperecord->background) ? '' : $unilabeltyperecord->background;
        $this->add_colourpicker($mform, $prefix.'background', get_string('background', 'unilabeltype_carousel'), $backgrounddefault);

        $mform->addElement('advcheckbox', $prefix.'usemobile', get_string('use_mobile_images', 'unilabeltype_carousel'));
        $mform->addHelpButton($prefix.'usemobile', 'use_mobile_images', 'unilabeltype_carousel');

        $repeatarray = [];
        // If we want each repeated elment in a numbered group we add a header with '{no}' in its label.
        // This is replaced by the number of element.
        $repeatarray[] = $mform->createElement('header', $prefix.'slidehdr', get_string('slide', 'unilabeltype_carousel').'-{no}');
        $repeatarray[] = $mform->createElement(
                                'editor',
                                $prefix.'caption',
                                get_string('caption', 'unilabeltype_carousel').'-{no}',
                                array('rows' => 4));
        $repeatarray[] = $mform->createElement(
                                'text',
                                $prefix.'url',
                                get_string('url', 'unilabeltype_carousel').'-{no}',
                                array('size' => 50));
        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix.'image',
            get_string('image', 'unilabeltype_carousel').'-{no}',
            null,
            [
                'maxbytes' => $form->get_course()->maxbytes,
                'maxfiles' => 1,
                'subdirs' => false,
                'accepted_types' => array('web_image'),
            ]
        );
        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix.'image_mobile',
            get_string('image_mobile', 'unilabeltype_carousel').'-{no}',
            null,
            [
                'maxbytes' => $form->get_course()->maxbytes,
                'maxfiles' => 1,
                'subdirs' => false,
                'accepted_types' => array('web_image'),
            ]
        );

        $repeatedoptions = [];
        $repeatedoptions[$prefix.'url']['type'] = PARAM_URL;
        $repeatedoptions[$prefix.'caption']['type'] = PARAM_RAW;
        $repeatedoptions[$prefix.'image']['type'] = PARAM_FILE;
        $repeatedoptions[$prefix.'image_mobile']['type'] = PARAM_FILE;
        // Adding the help buttons.
        $repeatedoptions[$prefix.'caption']['helpbutton'] = array('caption', 'unilabeltype_carousel');
        $repeatedoptions[$prefix.'url']['helpbutton'] = array('url', 'unilabeltype_carousel');
        $repeatedoptions[$prefix.'image_mobile']['helpbutton'] = array('image_mobile', 'unilabeltype_carousel');

        $defaultrepeatcount = 3; // The default count for slides.
        $repeatcount = count($this->slides);
        if ($rest = count($this->slides) % $defaultrepeatcount) {
            $repeatcount = count($this->slides) + ($defaultrepeatcount - $rest);
        }
        if ($repeatcount == 0) {
            $repeatcount = $defaultrepeatcount;
        }

        $nextel = $form->repeat_elements(
            $repeatarray,
            $repeatcount,
            $repeatedoptions,
            $prefix.'chosen_slides_count',
            $prefix.'add_more_slides_btn',
            $defaultrepeatcount, // Each time we add 3 elements.
            get_string('addmoreslides', 'unilabeltype_carousel'),
            true
        );
    }

    public function get_form_default($data, $unilabel) {
        global $DB;

        $cm = get_coursemodule_from_instance('unilabel', $unilabel->id);
        $context = \context_module::instance($cm->id);

        $prefix = 'unilabeltype_carousel_';

        // Set default data for the carousel in generel.
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $config = get_config('unilabeltype_carousel');
            $data[$prefix.'carouselinterval'] = $config->carouselinterval;
            $data[$prefix.'height'] = $config->height;
            $data[$prefix.'background'] = '#ffffff';
            $data[$prefix.'showintro'] = !empty($config->showintro);
            $data[$prefix.'usemobile'] = !empty($config->usemobile);
            return $data;
        }

        $data[$prefix.'carouselinterval'] = $unilabeltyperecord->carouselinterval;
        $data[$prefix.'height'] = $unilabeltyperecord->height;
        $data[$prefix.'background'] = $unilabeltyperecord->background;
        $data[$prefix.'showintro'] = $unilabeltyperecord->showintro;
        $data[$prefix.'usemobile'] = $unilabeltyperecord->usemobile;

        // Set default data for slides.
        if (!$slides = $DB->get_records('unilabeltype_carousel_slide',
                                            array('carouselid' => $unilabeltyperecord->id), 'id ASC')) {
            return $data;
        }

        $index = 0;
        foreach ($slides as $slide) {
            // Prepare the url field.
            $elementname = $prefix.'url['.$index.']';
            $data[$elementname] = $slide->url;

            // Prepare the caption field.
            $elementname = $prefix.'caption['.$index.']';
            $data[$elementname]['text'] = $slide->caption;
            $data[$elementname]['format'] = FORMAT_HTML;

            // Prepare the images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemid = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemid, $context->id, 'unilabeltype_carousel', 'image', $slide->id);
            $elementname = $prefix.'image['.$index.']';
            $data[$elementname] = $draftitemid;

            // Prepare the mobile images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemid = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemid, $context->id, 'unilabeltype_carousel', 'image_mobile', $slide->id);
            $elementname = $prefix.'image_mobile['.$index.']';
            $data[$elementname] = $draftitemid;
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
                'intro' => get_string('nocontent', 'unilabeltype_carousel'),
                'cmid' => $cm->id,
                'hasslides' => false,
            ];
        } else {
            $intro = $this->format_intro($unilabel, $cm);
            $showintro = !empty($unilabeltyperecord->showintro);
            $content = [
                'showintro' => $showintro,
                'intro' => $showintro ? $intro : '',
                'interval' => $unilabeltyperecord->carouselinterval,
                'height' => $unilabeltyperecord->height,
                'autoheight' => empty($unilabeltyperecord->height),
                'background' => $unilabeltyperecord->background,
                'slides' => array_values($this->slides),
                'hasslides' => count($this->slides) > 0,
                'cmid' => $cm->id,
            ];
        }
        $content = $renderer->render_from_template('unilabeltype_carousel/carousel', $content);

        return $content;
    }

    public function delete_content($unilabelid) {
        global $DB;

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabelid);

        // Delete all slides.
        if (!empty($unilabeltyperecord)) {
            $DB->delete_records('unilabeltype_carousel_slide', ['carouselid' => $unilabeltyperecord->id]);
        }

        $DB->delete_records('unilabeltype_carousel', ['unilabelid' => $unilabelid]);
    }

    public function save_content($formdata, $unilabel) {
        global $DB, $USER;

        // We want to keep the slides consistent so we start a transaction here.
        $transaction = $DB->start_delegated_transaction();

        $prefix = 'unilabeltype_carousel_';

        // First save the carousel record.
        if (!$unilabeltyperecord = $DB->get_record('unilabeltype_carousel', ['unilabelid' => $unilabel->id])) {
            $unilabeltyperecord = new \stdClass();
            $unilabeltyperecord->unilabelid = $unilabel->id;
            $unilabeltyperecord->id = $DB->insert_record('unilabeltype_carousel', $unilabeltyperecord);
        }

        $unilabeltyperecord->carouselinterval = $formdata->{$prefix.'carouselinterval'};
        $unilabeltyperecord->height = $formdata->{$prefix.'height'};
        $unilabeltyperecord->background = $formdata->{$prefix.'background'};
        $unilabeltyperecord->showintro = $formdata->{$prefix.'showintro'};
        $unilabeltyperecord->usemobile = $formdata->{$prefix.'usemobile'};

        $DB->update_record('unilabeltype_carousel', $unilabeltyperecord);

        $fs = get_file_storage();
        $context = \context_module::instance($formdata->cmid);
        $usercontext = \context_user::instance($USER->id);

        // First: remove old slide images.
        // We use the module_context as context and this component as component.
        $fs->delete_area_files($context->id, 'unilabeltype_carousel', 'image');
        $fs->delete_area_files($context->id, 'unilabeltype_carousel', 'image_mobile');

        // Second: remove old slide records.
        $DB->delete_records('unilabeltype_carousel_slide', array('carouselid' => $unilabeltyperecord->id));

        // How many slides could be defined (we have an array here)?
        // They may not all used so some could be left out.
        $potentialslidecount = $formdata->{$prefix.'chosen_slides_count'};
        for ($i = 0; $i < $potentialslidecount; $i++) {
            // Get the draftitemid to identify the submitted file.
            $draftitemid = $formdata->{$prefix.'image'}[$i];
            if (!empty($unilabeltyperecord->usemobile)) {
                $draftitemidmobile = $formdata->{$prefix.'image_mobile'}[$i];
            }
            // Do we have an image? We get this information with file_get_draft_area_info().
            $fileinfo = file_get_draft_area_info($draftitemid);

            // We only create a record if we have at least a file or a caption.
            $caption = $formdata->{$prefix.'caption'}[$i]['text'];
            if ($fileinfo['filecount'] < 1 AND !$this->html_has_content($caption)) {
                continue;
            }

            $sliderecord = new \stdClass();
            $sliderecord->carouselid = $unilabeltyperecord->id;
            $sliderecord->url = $formdata->{$prefix.'url'}[$i];
            $sliderecord->caption = $caption;

            $sliderecord->id = $DB->insert_record('unilabeltype_carousel_slide', $sliderecord);

            // Now we can save our draft files.
            file_save_draft_area_files($draftitemid, $context->id, 'unilabeltype_carousel', 'image', $sliderecord->id);
            if (!empty($formdata->{$prefix.'usemobile'})) {
                file_save_draft_area_files($draftitemidmobile,
                            $context->id,
                            'unilabeltype_carousel',
                            'image_mobile',
                            $sliderecord->id);
            }
        }

        $transaction->allow_commit();

        return !empty($unilabeltyperecord->id);
    }

    public function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            if (!$this->unilabeltyperecord = $DB->get_record('unilabeltype_carousel', ['unilabelid' => $unilabelid])) {
                $this->slides = array();
                return;
            }
            $this->cm = get_coursemodule_from_instance('unilabel', $unilabelid);
            $this->context = \context_module::instance($this->cm->id);

            $slides = $DB->get_records('unilabeltype_carousel_slide', array('carouselid' => $this->unilabeltyperecord->id));
            $index = 0;
            foreach ($slides as $slide) {
                $slide->imageurl = $this->get_image_for_slide($slide);
                $slide->imagemobileurl = $this->get_image_mobile_for_slide($slide);
                $slide->nr = $index;
                $index++;
            }
            $this->slides = $slides;
        }
        return $this->unilabeltyperecord;
    }

    private function get_image_for_slide($slide) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->context->id, 'unilabeltype_carousel', 'image', $slide->id, "", $includedirs = false);
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url($this->context->id,
            'unilabeltype_carousel',
            'image',
            $slide->id,
            '/',
            $file->get_filename()
        );
        return $imageurl;
    }

    private function get_image_mobile_for_slide($slide) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->context->id,
                                    'unilabeltype_carousel',
                                    'image_mobile',
                                    $slide->id,
                                    "",
                                    $includedirs = false);
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url($this->context->id,
            'unilabeltype_carousel',
            'image_mobile',
            $slide->id,
            '/',
            $file->get_filename()
        );
        return $imageurl;
    }

    private function add_colourpicker($mform, $name, $label, $defaultvalue) {
        global $PAGE;
        $mform->addElement('hidden', $name);
        $mform->setType($name, PARAM_TEXT);
        $renderer = $PAGE->get_renderer('mod_unilabel');
        $colourpickercontent = new \stdClass();
        $colourpickercontent->iconurl = $renderer->image_url('i/colourpicker');
        $colourpickercontent->inputname = $name;
        $colourpickercontent->inputid = 'id_'.$name.'_colourpicker';
        $colourpickercontent->label = $label;
        $colourpickercontent->defaultvalue = $defaultvalue;
        $colourpickerhtml = $renderer->render_from_template('unilabeltype_carousel/colourpicker', $colourpickercontent);
        $mform->addElement('html', $colourpickerhtml);
        $PAGE->requires->js_init_call('M.util.init_colour_picker', array($colourpickercontent->inputid, null));
    }

    private function html_has_content($caption) {
        $searches = array(
            '<br>',
            '<br />',
            '<p>',
            '</p>'
        );

        $check = trim(str_replace($searches, '', $caption));

        return !empty($check);
    }
}
