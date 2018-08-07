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

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_unilabel_mod_form extends moodleform_mod {

    function definition() {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));
        $this->standard_intro_elements(get_string('unilabeltext', 'mod_unilabel'));

        $plugins = \mod_unilabel\factory::get_plugin_list();
        $plugins = array(get_string('choose')) + $plugins;
        $mform->addElement('select', 'unilabeltype', get_string('labeltype', 'mod_unilabel'), $plugins);
        $mform->addRule('unilabeltype', get_string('required'), 'required', null, 'client');

        // if ($plugins = \core_component::get_plugin_list_with_class('unilabeltype', 'content')) {
        //     foreach ($plugins as $plugin => $classname) {
        //         if (!method_exists($classname, 'add_form_fragment')) {
        //             continue;
        //         }
        //         $mform->addElement('header', $plugin.'_hdr', get_string('pluginname', $plugin));
        //         $classname::add_form_fragment($mform);
        //     }
        // }

        // unilabel does not add "Show description" checkbox meaning that 'intro' is always shown on the course page.
        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons(true, false, null);

    }

}
