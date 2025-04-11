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
defined('MOODLE_INTERNAL') || die;

$plugin->version  = 2025020403;     // The current version.
$plugin->release  = '4.5.7 (Build: 2025040400)';
$plugin->maturity = MATURITY_STABLE;

$plugin->requires  = 2024100100;     // Requires Moodle 4.5
$plugin->component = 'mod_unilabel'; // Full name of the plugin (used for diagnostics).
$plugin->cron      = 0;
