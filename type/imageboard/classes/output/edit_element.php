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
 * unilabel type imageboard.
 *
 * @package     unilabeltype_imageboard
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unilabeltype_imageboard\output;

/**
 * Content type definition.
 * @package     unilabeltype_imageboard
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_element extends \mod_unilabel\output\edit_element_base {

    /**
     * Get the name of the elements group.
     *
     * @return string
     */
    public function get_elements_name() {
        return get_string('image', $this->component);
    }

    /**
     * Get the form elements as array in the order they should be printed out.
     *
     * @return \HTML_QuickForm_element[]
     */
    public function get_elements() {
        $elements = [];

        $inputidbase  = 'id_' . $this->prefix . 'url_';
        $pickerbutton = new \mod_unilabel\output\component\activity_picker_button($this->formid, $inputidbase);

        $elements[] = $this->get_textfield(
            'title',
            ['size' => 50]
        );
        $elements[] = $this->get_filemanager(
            'image',
            [],
            $this->manager_options()
        );

        // The position and the target are grouped elements built by:
        // xposition   and yposition
        // targetwidth and targetheight
        // Those both groups are position and targetsize.
        $xposition = $this->get_textfield(
            'xposition',
            ['size' => 4, 'placeholder' => get_string('placeholder_xposition', $this->component)]
        );
        $yposition = $this->get_textfield(
            'yposition',
            ['size' => 4, 'placeholder' => get_string('placeholder_yposition', $this->component)]
        );
        $elements[] = $this->get_group('position', [$xposition, $yposition], 'position');

        $targetwidth = $this->get_textfield(
            'targetwidth',
            ['size' => 4, 'placeholder' => get_string('placeholder_targetwidth', $this->component)]
        );
        $targetheight = $this->get_textfield(
            'targetheight',
            ['size' => 4, 'placeholder' => get_string('placeholder_targetheight', $this->component)]
        );
        $elements[] = $this->get_group('targetsize', [$targetwidth, $targetheight], 'targetsize');

        $urlelement = $this->get_textfield(
            'url',
            ['size' => 50]
        );
        $newwindowelement = $this->get_checkbox(
            'newwindow',
            [],
            '',
            get_string('newwindow')
        );
        $elements[] = $this->get_group(
            'urlgroup',
            [$urlelement, $newwindowelement],
            'url',
            get_string('url', $this->component) . '-' . ($this->repeatindex + 1)
        );

        $elements[] = $this->get_static(
            'picker',
            $this->output->render(
                $pickerbutton
            )
        );

        $numbers = array_combine(range(0, 10, 1), range(0, 10, 1));
        $elements[] = $this->get_select(
            'border',
            $numbers
        );

        return $elements;
    }

    /**
     * Get the options array for a file manager.
     *
     * @return array
     */
    public function manager_options() {
        return [
            'maxbytes'       => $this->course->maxbytes,
            'maxfiles'       => 1,
            'subdirs'        => false,
            'accepted_types' => ['web_image'],
        ];
    }

}
