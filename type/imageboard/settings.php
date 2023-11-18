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
 * Unilabel type imageboard
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Schenkel
 * @copyright   Andreas Schenkel {@link https://github.com/andreasschenkel}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('unilabeltype_imageboard', get_string('pluginname', 'unilabeltype_imageboard'));

$imageboardsettings = [];

$imageboardsettings[] = new admin_setting_configcheckbox('unilabeltype_imageboard/active',
    get_string('active'),
    '',
    true);

$numbers = array_combine(range(100, 1800, 50), range(100, 1800, 50));
$imageboardsettings[] = new admin_setting_configselect('unilabeltype_imageboard/default_canvaswidth',
    get_string('default_canvaswidth', 'unilabeltype_imageboard'),
    get_string('default_canvaswidth_help', 'unilabeltype_imageboard'),
    600,
    $numbers
);

$numbers = array_combine(range(100, 1800, 50), range(100, 1800, 50));
$imageboardsettings[] = new admin_setting_configselect('unilabeltype_imageboard/default_canvasheight',
    get_string('default_canvasheight', 'unilabeltype_imageboard'),
    get_string('default_canvasheight_help', 'unilabeltype_imageboard'),
    400,
    $numbers
);


$numbers = array_combine(range(0, 36, 1), range(0, 36, 1));
$imageboardsettings[] = new admin_setting_configselect('unilabeltype_imageboard/default_fontsize',
        get_string('default_fontsize', 'unilabeltype_imageboard'),
        get_string('default_fontsize_help', 'unilabeltype_imageboard'),
        12,
        $numbers
);

$imageboardsettings[] = new admin_setting_configcolourpicker('unilabeltype_imageboard/default_titlecolor',
        get_string('default_titlecolor', 'unilabeltype_imageboard'),
        get_string('default_titlecolor_desc', 'unilabeltype_imageboard')
        , '#fffffe');

$imageboardsettings[] = new admin_setting_configcolourpicker('unilabeltype_imageboard/default_titlebackgroundcolor',
        get_string('default_titlebackgroundcolor', 'unilabeltype_imageboard'),
        get_string('default_titlebackgroundcolor_desc', 'unilabeltype_imageboard')
        , '#110099');

$numbers = array_combine(range(0, 10, 1), range(0, 10, 1));
$imageboardsettings[] = new admin_setting_configselect('unilabeltype_imageboard/default_bordersize',
        get_string('default_bordersize', 'unilabeltype_imageboard'),
        get_string('default_bordersize_desc', 'unilabeltype_imageboard'),
        1,
        $numbers
);

$name = 'unilabeltype_imageboard/default_bordercolor';
$title = get_string('default_bordercolor', 'unilabeltype_imageboard');
$description = get_string('default_bordercolor_desc', 'unilabeltype_imageboard');
$imageboardsettings[] = new admin_setting_configcolourpicker($name, $title, $description, '#ff0000');



$name = 'unilabeltype_imageboard/default_gridcolor';
$title = get_string('default_gridcolor', 'unilabeltype_imageboard');
$description = get_string('default_gridcolor_desc', 'unilabeltype_imageboard');
$imageboardsettings[] = new admin_setting_configcolourpicker($name, $title, $description, '#0000ff');



$imageboardsettings[] = new admin_setting_configcheckbox('unilabeltype_imageboard/default_showintro',
    get_string('default_showintro', 'unilabeltype_imageboard'),
    '',
    false
);

foreach ($imageboardsettings as $setting) {
    $page->add($setting);
}

$settingscategory->add($page);
