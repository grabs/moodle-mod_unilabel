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
 * unilabel type carousel.
 *
 * @package     unilabeltype_carousel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_carousel;
use mod_unilabel\setting_configselect_button;

/**
 * Content type definition.
 * @package     unilabeltype_carousel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_type extends \mod_unilabel\content_type {
    /** @var \stdClass */
    private $unilabeltyperecord;

    /** @var array */
    private $slides;

    /** @var \stdClass */
    private $cm;

    /** @var \context */
    private $context;

    /** Caption styles to be used in the instances caption setting. */
    public const CAPTIONSTYLES = [
        'dark',
        'light',
    ];

    /**
     * Get true if the unilabeltype supports sortorder by using drag-and-drop.
     *
     * @return bool
     */
    public function use_sortorder() {
        return true;
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

        $unilabeltyperecord = $this->load_unilabeltype_record($form->unilabel->id);

        $mform  = $form->get_mform();
        $prefix = $this->component . '_';

        $mform->addElement('advcheckbox', $prefix . 'showintro', get_string('showunilabeltext', $this->component));

        $mform->addElement('header', $prefix . 'hdr', $this->get_name());
        $mform->addHelpButton($prefix . 'hdr', 'pluginname', $this->component);
        $mform->setExpanded($prefix . 'hdr', false);

        $mform->addElement(
            'checkbox',
            $prefix . 'autorun',
            get_string('autorun', 'mod_unilabel'),
            ''
        );
        $autorundefault = !empty($this->config->autorun);
        $mform->setDefault($prefix . 'autorun', $autorundefault);

        $numbers = array_combine(range(1, 10), range(1, 10));
        $mform->addElement(
            'select',
            $prefix . 'carouselinterval',
            get_string('carouselinterval', $this->component),
            $numbers
        );
        $mform->hideIf($prefix . 'carouselinterval', $prefix . 'autorun', 'notchecked');

        $numbers = array_combine(range(100, 600, 50), range(100, 600, 50));
        $numbers = [0 => get_string('autoheight', $this->component)] + $numbers;
        $mform->addElement('select', $prefix . 'height', get_string('height', $this->component), $numbers);
        $mform->addHelpButton($prefix . 'height', 'height', $this->component);

        $backgrounddefault = empty($unilabeltyperecord->background) ? '' : $unilabeltyperecord->background;
        $this->add_colourpicker($mform,
            $prefix . 'background',
            get_string('background', $this->component),
            $backgrounddefault);

        $mform->addElement('advcheckbox', $prefix . 'usemobile', get_string('use_mobile_images', $this->component));
        $mform->addHelpButton($prefix . 'usemobile', 'use_mobile_images', $this->component);

        $captionoptions = $this->get_captionstyle_options(true);
        $mform->addElement('select', $prefix . 'captionstyle', get_string('captionstyle', $this->component), $captionoptions);
        $mform->addHelpButton($prefix . 'captionstyle', 'captionstyle', $this->component);

        $mform->addElement('text', $prefix . 'captionwidth', get_string('captionwidth', $this->component));
        $mform->setType($prefix . 'captionwidth', PARAM_INT);
        $mform->addHelpButton($prefix . 'captionwidth', 'captionwidth', $this->component);

        // Prepare the activity url picker.
        $formid       = $mform->getAttribute('id');
        $course       = $form->get_course();
        $picker       = new \mod_unilabel\output\component\activity_picker($course, $formid);
        $inputidbase  = 'id_' . $prefix . 'url_';
        $urltitleinputidbase  = 'id_' . $prefix . 'urltitle_';
        $pickerbutton = new \mod_unilabel\output\component\activity_picker_button($formid, $inputidbase, $urltitleinputidbase);
        $mform->addElement('html', $OUTPUT->render($picker));

        $repeatarray = [];
        // If we want each repeated elment in a numbered group we add a header with '{no}' in its label.
        // This is replaced by the number of element.
        $repeatarray[] = $mform->createElement(
            'header',
            'singleelementheader',
            get_string('slide', $this->component) . '-{no}'
        );

        $repeatarray[] = $mform->createElement(
            'hidden',
            $prefix . 'sortorder'
        );

        $repeatarray[] = $mform->createElement(
            'editor',
            $prefix . 'caption',
            get_string('caption', $this->component) . '-{no}',
            ['rows' => 4],
            $this->editor_options($form->context)
        );
        $repeatarray[] = $mform->createElement(
            'static',
            $prefix . 'activitypickerbutton',
            '',
            $OUTPUT->render($pickerbutton)
        );
        $urlelement = $mform->createElement(
            'text',
            $prefix . 'url',
            get_string('url', $this->component) . '-{no}',
            ['size' => 50]
        );
        $mform->setType($prefix . 'url', PARAM_URL);
        $newwindowelement = $mform->createElement(
            'checkbox',
            $prefix . 'newwindow',
            get_string('newwindow')

        );
        $repeatarray[] = $mform->createElement(
            'group',
            $prefix . 'urlgroup',
            get_string('url', $this->component) . '-{no}',
            [$urlelement, $newwindowelement],
            null,
            false
        );
        $repeatarray[] = $mform->createElement(
            'text',
            $prefix . 'urltitle',
            get_string('urltitle', $this->component) . '-{no}',
            ['size' => 50]
        );

        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix . 'image',
            get_string('image', $this->component) . '-{no}',
            null,
            $this->manager_options()
        );
        $repeatarray[] = $mform->createElement(
            'filemanager',
            $prefix . 'image_mobile',
            get_string('image_mobile', $this->component) . '-{no}',
            null,
            $this->manager_options()
        );

        $repeatedoptions                                   = [];
        $repeatedoptions[$prefix . 'sortorder']['type']    = PARAM_INT;
        $repeatedoptions[$prefix . 'url']['type']          = PARAM_URL;
        $repeatedoptions[$prefix . 'urltitle']['type']     = PARAM_TEXT;
        $repeatedoptions[$prefix . 'caption']['type']      = PARAM_RAW;
        $repeatedoptions[$prefix . 'image']['type']        = PARAM_FILE;
        $repeatedoptions[$prefix . 'image_mobile']['type'] = PARAM_FILE;
        // Adding the help buttons.
        $repeatedoptions[$prefix . 'caption']['helpbutton']      = ['caption', $this->component];
        $repeatedoptions[$prefix . 'urlgroup']['helpbutton']     = ['url', $this->component];
        $repeatedoptions[$prefix . 'image_mobile']['helpbutton'] = ['image_mobile', $this->component];

        $defaultrepeatcount = 1; // The default count for slides.
        $repeatcount        = count($this->slides);

        $nextel = $form->repeat_elements(
            $repeatarray,
            $repeatcount,
            $repeatedoptions,
            'multiple_chosen_elements_count', // We need a fixed name here to get drag and drop work.
            $prefix . 'add_more_elements_btn', // This element musst be called so to get removed when dnd is enabled.
            $defaultrepeatcount, // Each time we add 3 elements.
            get_string('addmoreslides', $this->component),
            false
        );

        // This elements are needed by js to set empty hidden fields while deleting an element.
        $myelements = [
            'caption',
            'urltitle',
            'url',
            'image',
            'image_mobile',
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

        // Set default data for the carousel in generel.
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $data[$prefix . 'carouselinterval'] = $this->config->carouselinterval;
            $data[$prefix . 'autorun']          = $this->config->autorun;
            $data[$prefix . 'height']           = $this->config->height;
            $data[$prefix . 'background']       = '#ffffff';
            $data[$prefix . 'showintro']        = !empty($this->config->showintro);
            $data[$prefix . 'usemobile']        = !empty($this->config->usemobile);
            $data[$prefix . 'captionwidth']     = 0;

            return $data;
        }

        $data[$prefix . 'carouselinterval'] = $unilabeltyperecord->carouselinterval;
        $data[$prefix . 'autorun']          = (bool) (!empty($unilabeltyperecord->carouselinterval));
        $data[$prefix . 'height']           = $unilabeltyperecord->height;
        $data[$prefix . 'background']       = $unilabeltyperecord->background;
        $data[$prefix . 'showintro']        = $unilabeltyperecord->showintro;
        $data[$prefix . 'usemobile']        = $unilabeltyperecord->usemobile;
        $data[$prefix . 'captionstyle']     = $unilabeltyperecord->captionstyle;
        $data[$prefix . 'captionwidth']     = $unilabeltyperecord->captionwidth;

        // Set default data for slides.
        $slides = $DB->get_records(
            'unilabeltype_carousel_slide',
            [
                'carouselid' => $unilabeltyperecord->id,
            ],
            'sortorder ASC'
        );
        if (!$slides) {
            return $data;
        }

        $index = 0;
        foreach ($slides as $slide) {
            // Prepare the url field.
            $elementname        = $prefix . 'url[' . $index . ']';
            $data[$elementname] = $slide->url;

            // Prepare the urltitle field.
            $elementname        = $prefix . 'urltitle[' . $index . ']';
            $data[$elementname] = $slide->urltitle ?? '';

            // Prepare the newwindow field.
            $elementname = $prefix . 'newwindow[' . $index . ']';
            $data[$elementname] = $slide->newwindow;

            // Prepare the caption field.
            $elementname                  = $prefix . 'caption[' . $index . ']';
            $data[$elementname]['format'] = FORMAT_HTML;
            $draftitemid                  = file_get_submitted_draft_itemid($elementname);
            $data[$elementname]['text']   = file_prepare_draft_area(
                $draftitemid,
                $context->id,
                $this->component,
                'caption',
                $slide->id,
                null,
                $slide->caption
            );
            $data[$elementname]['itemid'] = $draftitemid;

            // Prepare the images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemid = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemid, $context->id, $this->component, 'image', $slide->id);
            $elementname        = $prefix . 'image[' . $index . ']';
            $data[$elementname] = $draftitemid;

            // Prepare the mobile images.
            // $draftitemid is set by the function file_prepare_draft_area().
            $draftitemid = 0; // This is needed to create a new draftitemid.
            file_prepare_draft_area($draftitemid, $context->id, $this->component, 'image_mobile', $slide->id);
            $elementname        = $prefix . 'image_mobile[' . $index . ']';
            $data[$elementname] = $draftitemid;

            // Prepare the sortorder field.
            $elementname        = $prefix . 'sortorder[' . $index . ']';
            $data[$elementname] = $slide->sortorder ?? ($index + 1);

            ++$index;
        }
        return $data;
    }

    /**
     * Validate all form values given in $data and returns an array with errors.
     * It does the same as the validation method in moodle forms.
     *
     * @param  array $errors
     * @param  array $data
     * @param  array $files
     * @return array
     */
    public function form_validation($errors, $data, $files) {
        $prefix = $this->component . '_';
        if (!empty($data[$prefix . 'background'])) {
            if (!\mod_unilabel\configcolourpicker_validation::validate_colourdata($data[$prefix . 'background'])) {
                $errors[$prefix . 'background'] = get_string('invalidvalue', 'mod_unilabel');
            }
        }

        return $errors;
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
        if (!$unilabeltyperecord = $this->load_unilabeltype_record($unilabel->id)) {
            $content = [
                'intro'     => get_string('nocontent', $this->component),
                'cmid'      => $cm->id,
                'hasslides' => false,
            ];
        } else {
            $intro     = $this->format_intro($unilabel, $cm);
            $showintro = !empty($unilabeltyperecord->showintro);
            $content   = [
                'showintro'    => $showintro,
                'intro'        => $showintro ? $intro : '',
                'interval'     => $unilabeltyperecord->carouselinterval,
                'height'       => $unilabeltyperecord->height,
                'autoheight'   => empty($unilabeltyperecord->height),
                'background'   => $unilabeltyperecord->background,
                'hasslides'    => count($this->slides) > 0,
                'cmid'         => $cm->id,
                'plugin'       => $this->component,
                'captionstyle' => $unilabeltyperecord->captionstyle,
                'captionwidth' => $unilabeltyperecord->captionwidth,
            ];
            $content['slides'] = array_values(
                array_map(function ($slide) {
                    $slide->caption = file_rewrite_pluginfile_urls(
                        $slide->caption,
                        'pluginfile.php',
                        $this->context->id,
                        $this->component,
                        'caption',
                        $slide->id
                    );
                    return $slide;
                }, $this->slides)
            );

            if (!empty($this->config->custombutton)) {
                $fontbuttons = setting_configselect_button::get_font_buttons();
                $content['custombuttons']   = 1;
                $content['fontawesomenext'] = $fontbuttons[$this->config->custombutton]['next'];
                $content['fontawesomeprev'] = $fontbuttons[$this->config->custombutton]['prev'];

                // To make sure we have clean html we have to put the carousel css into the <head> by using javascript.
                $cssstring                = $renderer->render_from_template('mod_unilabel/carousel_button_style', $content);
                $content['cssjsonstring'] = json_encode($cssstring);
            }
        }

        $content = $renderer->render_from_template('unilabeltype_carousel/carousel', $content);

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

        $unilabeltyperecord = $this->load_unilabeltype_record($unilabelid);

        // Delete all slides.
        if (!empty($unilabeltyperecord)) {
            $DB->delete_records('unilabeltype_carousel_slide', ['carouselid' => $unilabeltyperecord->id]);
        }

        $DB->delete_records('unilabeltype_carousel', ['unilabelid' => $unilabelid]);
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

        // We want to keep the slides consistent so we start a transaction here.
        $transaction = $DB->start_delegated_transaction();

        $prefix = $this->component . '_';

        // First save the carousel record.
        if (!$unilabeltyperecord = $DB->get_record('unilabeltype_carousel', ['unilabelid' => $unilabel->id])) {
            $unilabeltyperecord             = new \stdClass();
            $unilabeltyperecord->unilabelid = $unilabel->id;
            $unilabeltyperecord->id         = $DB->insert_record('unilabeltype_carousel', $unilabeltyperecord);
        }

        if (!empty($formdata->{$prefix . 'autorun'})) {
            $unilabeltyperecord->carouselinterval = $formdata->{$prefix . 'carouselinterval'};
        } else {
            $unilabeltyperecord->carouselinterval = 0;
        }
        $unilabeltyperecord->height       = $formdata->{$prefix . 'height'};
        $unilabeltyperecord->background   = $formdata->{$prefix . 'background'};
        $unilabeltyperecord->showintro    = $formdata->{$prefix . 'showintro'};
        $unilabeltyperecord->usemobile    = $formdata->{$prefix . 'usemobile'};
        $unilabeltyperecord->captionstyle = $formdata->{$prefix . 'captionstyle'};
        $unilabeltyperecord->captionwidth = $formdata->{$prefix . 'captionwidth'};

        $DB->update_record('unilabeltype_carousel', $unilabeltyperecord);

        $fs          = get_file_storage();
        $context     = \context_module::instance($formdata->cmid);
        $usercontext = \context_user::instance($USER->id);

        // First: remove old slide images.
        // We use the module_context as context and this component as component.
        $fs->delete_area_files($context->id, $this->component, 'caption');
        $fs->delete_area_files($context->id, $this->component, 'image');
        $fs->delete_area_files($context->id, $this->component, 'image_mobile');

        // Second: remove old slide records.
        $DB->delete_records('unilabeltype_carousel_slide', ['carouselid' => $unilabeltyperecord->id]);

        // How many slides could be defined (we have an array here)?
        // They may not all used so some could be left out.
        $potentialslidecount = $formdata->multiple_chosen_elements_count;
        for ($i = 0; $i < $potentialslidecount; ++$i) {
            // Get the draftitemid to identify the submitted file.
            $draftitemid = $formdata->{$prefix . 'image'}[$i];
            if (!empty($unilabeltyperecord->usemobile)) {
                $draftitemidmobile = $formdata->{$prefix . 'image_mobile'}[$i];
            }
            // Do we have an image? We get this information with file_get_draft_area_info().
            $fileinfo = file_get_draft_area_info($draftitemid);

            // We only create a record if we have at least a file or a caption.
            $caption = $formdata->{$prefix . 'caption'}[$i]['text'] ?? '';
            if ($fileinfo['filecount'] < 1 && !$this->html_has_content($caption)) {
                continue;
            }

            // Rewrite file url in caption.
            // Get the draftitemid for caption editor.
            $drafitemidcaption = $formdata->{$prefix . 'caption'}[$i]['itemid'];
            $caption = file_rewrite_urls_to_pluginfile($caption, $drafitemidcaption);
            $urltitle   = $formdata->{$prefix . 'urltitle'}[$i];

            $sortorder = $formdata->{$prefix . 'sortorder'}[$i];

            $sliderecord             = new \stdClass();
            $sliderecord->carouselid = $unilabeltyperecord->id;
            $sliderecord->urltitle   = $urltitle;
            $sliderecord->url        = $formdata->{$prefix . 'url'}[$i];
            $sliderecord->newwindow = !empty($formdata->{$prefix . 'newwindow'}[$i]);
            $sliderecord->caption    = $caption;
            $sliderecord->sortorder  = $sortorder;

            $sliderecord->id = $DB->insert_record('unilabeltype_carousel_slide', $sliderecord);

            // Now we can save our draft files.
            file_save_draft_area_files(
                $drafitemidcaption,
                $context->id,
                $this->component,
                'caption',
                $sliderecord->id
            );
            file_save_draft_area_files(
                $draftitemid,
                $context->id,
                $this->component,
                'image',
                $sliderecord->id
            );
            if (!empty($formdata->{$prefix . 'usemobile'})) {
                file_save_draft_area_files(
                    $draftitemidmobile,
                    $context->id,
                    $this->component,
                    'image_mobile',
                    $sliderecord->id
                );
            }
        }

        $transaction->allow_commit();

        return !empty($unilabeltyperecord->id);
    }

    /**
     * Load and cache the unilabel record.
     *
     * @param  int       $unilabelid
     * @return \stdClass
     */
    public function load_unilabeltype_record($unilabelid) {
        global $DB;

        if (empty($this->unilabeltyperecord)) {
            if (!$this->unilabeltyperecord = $DB->get_record('unilabeltype_carousel', ['unilabelid' => $unilabelid])) {
                $this->slides = [];

                return;
            }
            $this->cm      = get_coursemodule_from_instance('unilabel', $unilabelid);
            $this->context = \context_module::instance($this->cm->id);

            $slides = $DB->get_records(
                'unilabeltype_carousel_slide',
                [
                    'carouselid' => $this->unilabeltyperecord->id,
                ],
                'sortorder ASC'
            );
            $index  = 0;
            foreach ($slides as $slide) {
                $slide->imageurl       = $this->get_image_for_slide($slide);
                $slide->imagemobileurl = $this->get_image_mobile_for_slide($slide);
                $slide->nr             = $index;
                $slide->captionplain   = format_string($slide->caption);
                ++$index;
            }
            $this->slides = $slides;
        }

        return $this->unilabeltyperecord;
    }

    /**
     * Get the image url for the given slide.
     *
     * @param  \stdClass $slide
     * @return string
     */
    private function get_image_for_slide($slide) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->context->id, $this->component, 'image', $slide->id, '', $includedirs = false);
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url(
            $this->context->id,
            $this->component,
            'image',
            $slide->id,
            '/',
            $file->get_filename()
        );

        return $imageurl;
    }

    /**
     * Get the mobile image url.
     *
     * @param  \stdClass $slide
     * @return string
     */
    private function get_image_mobile_for_slide($slide) {
        $fs = get_file_storage();

        $files = $fs->get_area_files(
            $this->context->id,
            $this->component,
            'image_mobile',
            $slide->id,
            '',
            false
        );
        if (!$file = array_shift($files)) {
            return '';
        }
        $imageurl = \moodle_url::make_pluginfile_url(
            $this->context->id,
            $this->component,
            'image_mobile',
            $slide->id,
            '/',
            $file->get_filename()
        );

        return $imageurl;
    }

    /**
     * Check whether ther is content or not.
     *
     * @param  string $caption
     * @return bool
     */
    private function html_has_content($caption) {
        $searches = [
            '<br>',
            '<br />',
            '<p>',
            '</p>',
        ];

        $check = trim(str_replace($searches, '', $caption));

        return !empty($check);
    }

    /**
     * Get the options array to support files in editor.
     *
     * @param  \context $context
     * @return array
     */
    public function editor_options($context) {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean'  => true,
            'context'  => $context,
            'subdirs'  => true,
        ];
    }

    /**
     * Get the options array for a file manager.
     *
     * @return array
     */
    public function manager_options() {
        return [
            'maxfiles'       => 1,
            'subdirs'        => false,
            'accepted_types' => ['web_image'],
        ];
    }

    /**
     * Generates an array with options to define a css class.
     * The structure is like:
     * [
     *     'light' => 'captionstyle_light',
     *     'dark'  => 'captionstyle_dark',
     * ]
     *
     * @param bool $addchoose If true an additional element "Choose" is add at the beginning.
     * @return array
     */
    public function get_captionstyle_options($addchoose = false) {
        $options = [];
        foreach (static::CAPTIONSTYLES as $style) {
            $options[$style] = get_string('captionstyle_' . $style, $this->component);
        }
        if ($addchoose) {
            $options = ['' => get_string('choose')] + $options;
        }
        return $options;
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
