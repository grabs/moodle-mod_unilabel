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
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel;

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/adminlib.php');

/**
 * Content type definition.
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_configselect_button extends \admin_setting {
    /**
     * Options used in select box and also in css templates.
     *
     * @var array
     */
    private $options;

    /**
     * List of css codes for fontawesome.
     *
     * @var array
     */
    public $buttonlist = [];

    /**
     * Constructor.
     * @param  string $name           unique ascii name
     * @param  string $visiblename    localised
     * @param  string $description    long localised info
     * @param  string $defaultsetting
     * @return void
     */
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        $this->buttonlist = static::get_font_buttons();
        $this->options = [0 => 0];
        foreach ($this->buttonlist as $key => $btn) {
            $this->options[$key] = $btn['next'];
        }
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Retrieves the current setting using the objects name.
     *
     * @return string
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Sets the value for the setting.
     *
     * Sets the value for the setting to either the yes or no values
     * of the object by comparing $data to yes
     *
     * @param  mixed  $data Gets converted to str for comparison against yes value
     * @return string empty string or error
     */
    public function write_setting($data) {
        if (empty($data)) {
            $data = 0;
        }

        return $this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin');
    }

    /**
     * Returns an XHTML checkbox field.
     *
     * @param  string $data  If $data matches yes then checkbox is checked
     * @param  string $query
     * @return string XHTML field
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        // Get the string for default setting.
        $defaultinfo = $default;
        if (empty($default)) {
            $defaultinfo = get_string('theme');
        }

        $values       = [];
        $currenttitle = get_string('choose');
        foreach ($this->options as $key => $id) {
            $value        = new \stdClass();
            $value->value = $key;
            $value->title = $id;
            if ($value->value == $data) {
                $value->checked = 'checked';
                $currentvalue   = $value->value;
            }
            if ($key == 0) {
                $value->title  = $defaultinfo;
                $value->istext = 1;
            }
            $values[] = $value;
        }

        $context = (object) [
            'id'           => $this->get_id(),
            'name'         => $this->get_full_name(),
            'values'       => $values,
            'currentvalue' => $this->options[$currentvalue],
        ];
        if ($currentvalue == 0) {
            $context->currentvalue       = $defaultinfo;
            $context->currentvalueistext = 1;
        }

        // To make sure we have clean html we have to put the carousel css into the <head> by using javascript.
        $cssstring              = $OUTPUT->render_from_template('mod_unilabel/setting_configselect_style', $context);
        $context->cssjsonstring = json_encode($cssstring);

        $element = $OUTPUT->render_from_template('mod_unilabel/setting_configselect', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $defaultinfo, $query);
    }

    /**
     * Get an array with a font awesome button definition. Depending on the Moodle
     * version it returns font awesome 4.7 or 6.0 free.
     *
     * @return array
     */
    public static function get_font_buttons() {
        // Since Moodle 4.2 the new FontAwesome 6 is used.
        if (version_compare(moodle_major_version(), '4.2', '<')) {
            // The current Moodle version is less then 4.2. So we load the fontawesome 4.7 items.
            return [
                '1' => ['next' => 'fa fa-angle-right fa-xl',          'prev' => 'fa fa-angle-left fa-xl'],
                '2' => ['next' => 'fa fa-angle-double-right fa-xl',   'prev' => 'fa fa-angle-double-left fa-xl'],
                '3' => ['next' => 'fa fa-forward fa-xl',              'prev' => 'fa fa-backward fa-xl'],
                '4' => ['next' => 'fa fa-caret-right fa-xl',          'prev' => 'fa fa-caret-left fa-xl'],
                '5' => ['next' => 'fa fa-hand-o-right fa-xl',         'prev' => 'fa fa-hand-o-left fa-xl'],
                '6' => ['next' => 'fa fa-arrow-right fa-xl',          'prev' => 'fa fa-arrow-left fa-xl'],
                '7' => ['next' => 'fa fa-arrow-circle-right fa-xl',   'prev' => 'fa fa-arrow-circle-left fa-xl'],
                '8' => ['next' => 'fa fa-arrow-circle-o-right fa-xl', 'prev' => 'fa fa-arrow-circle-o-left fa-xl'],
            ];
        } else {
            // The current Moodle version is 4.2 or later. So we load the fontawesome 6 items.
            return [
                '1' => ['next' => 'fa fa-solid fa-angle-right fa-xl',  'prev' => 'fa fa-solid fa-angle-left fa-xl'],
                '2' => ['next' => 'fa fa-solid fa-angles-right fa-xl', 'prev' => 'fa fa-solid fa-angles-left fa-xl'],
                '3' => ['next' => 'fa fa-solid fa-forward',            'prev' => 'fa fa-solid fa-backward'],
                '4' => ['next' => 'fa fa-solid fa-caret-right fa-xl',  'prev' => 'fa fa-solid fa-caret-left fa-xl'],
                '5' => ['next' => 'fa fa-regular fa-hand-point-right', 'prev' => 'fa fa-regular fa-hand-point-left'],
                '6' => ['next' => 'fa fa-solid fa-arrow-right fa-xl',  'prev' => 'fa fa-solid fa-arrow-left fa-xl'],
                '7' => ['next' => 'fa fa-regular fa-circle-right',     'prev' => 'fa fa-regular fa-circle-left'],
                '8' => ['next' => 'fa fa-solid fa-circle-arrow-right', 'prev' => 'fa fa-solid fa-circle-arrow-left'],
            ];
        }

    }
}
