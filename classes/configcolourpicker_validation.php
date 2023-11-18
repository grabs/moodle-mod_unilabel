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
 * Class to check color values.
 *
 * We only use the admin_setting_configcolourpicker class to use its method to check the color.
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class configcolourpicker_validation extends \admin_setting_configcolourpicker {
    /**
     * Validates the colour that was entered by the user.
     *
     * @param  string       $data
     * @return string|false
     */
    public static function validate_colourdata($data) {
        $instance = new parent('dummy', 'dummy', '', '');

        return $instance->validate($data);
    }
}
