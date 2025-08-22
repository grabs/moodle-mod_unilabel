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
 * unilabel module.
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel;

/**
 * Placeholder class if an active type is currently not installed or otherwise not available.
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tinymce_helper extends \editor_tiny\editor {
    /**
     * Get an escaped and json encoded configuration object for reinitialize an tinymce instance by js.
     * Most of this code comes from editor_tiny\editor::use_editor in lib/editor/tiny/classes/editor.php.
     *
     * @param  array  $editoroptions
     * @param  int    $draftitemid
     * @return string The json encode configuration object
     */
    public function get_options(array $editoroptions = [], int $draftitemid = 0) {
        global $PAGE;

        list($options, $fpoptions) = static::split_editor_options(
            $editoroptions,
            $draftitemid
        );

        // Ensure that the default configuration is set.
        self::set_default_configuration($this->manager);

        if ($fpoptions === null) {
            $fpoptions = [];
        }

        $context = $PAGE->context;

        if (isset($options['context']) && ($options['context'] instanceof \context)) {
            // A different context was provided.
            // Use that instead.
            $context = $options['context'];
        }

        // Generate the configuration for this editor.
        $siteconfig = get_config('editor_tiny');
        $config     = (object) [
            // The URL to the CSS file for the editor.
            'css' => $PAGE->theme->editor_css_url()->out(false),

            // The current context for this page or editor.
            'context' => $context->id,

            // File picker options.
            'filepicker' => $fpoptions,

            'currentLanguage' => current_language(),

            'branding' => property_exists($siteconfig, 'branding') ? !empty($siteconfig->branding) : true,

            // Language options.
            'language' => [
                'currentlang' => current_language(),
                'installed'   => get_string_manager()->get_list_of_translations(true),
                'available'   => get_string_manager()->get_list_of_languages(),
            ],

            // Placeholder selectors.
            // Some contents (Example: placeholder elements) are only shown in the editor, and not to users. It is unrelated to the
            // real display. We created a list of placeholder selectors, so we can decide to or not to apply rules, styles... to
            // these elements.
            // The default of this list will be empty.
            // Other plugins can register their placeholder elements to placeholderSelectors list by calling
            // editor_tiny/options::registerPlaceholderSelectors.
            'placeholderSelectors' => [],

            // Plugin configuration.
            'plugins' => $this->manager->get_plugin_configuration($context, $options, $fpoptions, $this),

            // Nest menu inside parent DOM.
            'nestedmenu' => true,
        ];

        if (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING) {
            // Add sample selectors for Behat test.
            $config->placeholderSelectors = ['.behat-tinymce-placeholder'];
        }

        foreach ($fpoptions as $fp) {
            // Guess the draftitemid for the editor.
            // Note: This is the best we can do at the moment.
            if (!empty($fp->itemid)) {
                $config->draftitemid = $fp->itemid;
                break;
            }
        }

        $configoptions = json_encode(convert_to_array($config), JSON_UNESCAPED_SLASHES + JSON_PRETTY_PRINT);
        // We have to escape the output because it gets directly into js.
        $configoptions = str_replace('\\', '\\\\', $configoptions);
        // However, we must not double-escape quotes to avoid screwing up the JSON,
        // so revert them back to single backslashes.
        $configoptions = str_replace('\\\\"', '\\"', $configoptions);
        return $configoptions;
    }

    /**
     * Extract the filepicker options.
     * Most of this code comes from MoodleQuickForm_editor::toHtml in lib/form/editor.php.
     *
     * @param  array $options
     * @param  int   $draftitemid
     * @return array
     */
    public static function split_editor_options(array $options, int $draftitemid = 0) {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');

        $defaultoptions = [
            'subdirs'               => 0,
            'maxbytes'              => 0,
            'maxfiles'              => 0,
            'changeformat'          => 0,
            'areamaxbytes'          => FILE_AREA_MAX_BYTES_UNLIMITED,
            'context'               => null,
            'noclean'               => 0,
            'trusttext'             => 0,
            'return_types'          => 15,
            'enable_filemanagement' => true,
            'removeorphaneddrafts'  => false,
            'autosave'              => true,
        ];

        $options = array_merge($defaultoptions, $options);

        /** @var \context $ctx */
        $ctx = $options['context'];

        $maxfiles = $options['maxfiles'];

        // Get filepicker info.
        //
        $fpoptions = [];
        if ($maxfiles != 0 && $draftitemid > 0) {
            $args = new \stdClass();
            // Need these three to filter repositories list.
            $args->accepted_types = ['web_image'];
            $args->return_types   = $options['return_types'];
            $args->context        = $ctx;
            $args->env            = 'filepicker';
            // Advimage plugin.
            $imageoptions               = initialise_filepicker($args);
            $imageoptions->context      = $ctx;
            $imageoptions->client_id    = uniqid();
            $imageoptions->maxbytes     = $options['maxbytes'];
            $imageoptions->areamaxbytes = $options['areamaxbytes'];
            $imageoptions->env          = 'editor';
            $imageoptions->itemid       = $draftitemid;

            // Moodlemedia plugin.
            $args->accepted_types        = ['video', 'audio'];
            $mediaoptions               = initialise_filepicker($args);
            $mediaoptions->context      = $ctx;
            $mediaoptions->client_id    = uniqid();
            $mediaoptions->maxbytes     = $options['maxbytes'];
            $mediaoptions->areamaxbytes = $options['areamaxbytes'];
            $mediaoptions->env          = 'editor';
            $mediaoptions->itemid       = $draftitemid;

            // Advlink plugin.
            $args->accepted_types       = '*';
            $linkoptions               = initialise_filepicker($args);
            $linkoptions->context      = $ctx;
            $linkoptions->client_id    = uniqid();
            $linkoptions->maxbytes     = $options['maxbytes'];
            $linkoptions->areamaxbytes = $options['areamaxbytes'];
            $linkoptions->env          = 'editor';
            $linkoptions->itemid       = $draftitemid;

            $args->accepted_types           = ['.vtt'];
            $subtitleoptions               = initialise_filepicker($args);
            $subtitleoptions->context      = $ctx;
            $subtitleoptions->client_id    = uniqid();
            $subtitleoptions->maxbytes     = $options['maxbytes'];
            $subtitleoptions->areamaxbytes = $options['areamaxbytes'];
            $subtitleoptions->env          = 'editor';
            $subtitleoptions->itemid       = $draftitemid;

            if (has_capability('moodle/h5p:deploy', $ctx)) {
                // Only set H5P Plugin settings if the user can deploy new H5P content.
                // H5P plugin.
                $args->accepted_types     = ['.h5p'];
                $h5poptions               = initialise_filepicker($args);
                $h5poptions->context      = $ctx;
                $h5poptions->client_id    = uniqid();
                $h5poptions->maxbytes     = $options['maxbytes'];
                $h5poptions->areamaxbytes = $options['areamaxbytes'];
                $h5poptions->env          = 'editor';
                $h5poptions->itemid       = $draftitemid;
                $fpoptions['h5p']         = $h5poptions;
            }

            $fpoptions['image']    = $imageoptions;
            $fpoptions['media']    = $mediaoptions;
            $fpoptions['link']     = $linkoptions;
            $fpoptions['subtitle'] = $subtitleoptions;
        }

        return [$options, $fpoptions];
    }

    /**
     * Checks whether or not tinymce is the current editor.
     * This is needed because the drag and drop feature does not fully support this editor.
     *
     * @return bool
     */
    public static function tiny_active() {
        $editor = editors_get_preferred_editor();
        if (get_class($editor) == 'editor_tiny\editor') {
            return true;
        }

        return false;
    }
}
