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
 * mod_unilabel edit content page.
 *
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_unilabel\output\component;

/**
 * Edit page for an unilabeltype
 * @package     mod_unilabel
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_content_page implements \renderable, \templatable {
    /** @var array */
    public $data = [];

    /** @var \core\output\local\dropdown\status */
    protected $dropdown;

    /**
     * Constructor for the edit content page component.
     *
     * Initializes the type selector dropdown and prepares the form HTML for rendering.
     *
     * @param int $cmid The course module ID
     * @param \mod_unilabel\content_type $currenttype The current content type instance
     * @param \mod_unilabel\edit_content_form $mform The form instance for content editing
     * @param array $pluginlist List of available content types with shortname => fullname pairs
     */
    public function __construct(
        int $cmid,
        \mod_unilabel\content_type $currenttype,
        \mod_unilabel\edit_content_form $mform,
        array $pluginlist
    ) {
        // Create base URL for the edit content page with course module ID.
        $mybaseurl = new \moodle_url('/mod/unilabel/edit_content.php', ['cmid' => $cmid]);
        $select = new \core\output\choicelist();

        // Iterate through all available unilabel plugin types to create dropdown options.
        foreach ($pluginlist as $pluginshortname => $pluginfullname) {
            // Create URL for switching to this specific plugin type.
            if ($pluginshortname != $currenttype->get_type()) {
                $switchurl = new \moodle_url($mybaseurl, ['sesskey' => sesskey(), 'switchtype' => $pluginshortname]);
            } else {
                $switchurl = null;
            }
            $definition = [
                'description' => \mod_unilabel\factory::get_type_info($pluginshortname),
            ];
            if (!empty($switchurl)) {
                $definition['url'] = $switchurl;
            }
            $select->add_option(
                $pluginshortname,
                $pluginfullname,
                $definition,
            );
        }

        // Set the currently active plugin type as the selected value in the dropdown.
        $select->set_selected_value($currenttype->get_type());

        // Create a styled dropdown component for selecting the unilabel type.
        $this->dropdown = new \core\output\local\dropdown\status(
            $currenttype->get_name(),
            $select,
            [
                'extras' => ['id' => 'unilabeltypechooser'],
                'buttonclasses' => 'form-select dropdown-toggle',
                'classes' => 'my-2',
            ]
        );

        // Get detailed information about the current plugin type for display.
        $elementinfo = \mod_unilabel\factory::get_type_info($currenttype->get_type(), true);

        // Store the element information and form HTML in the template data.
        $this->data['elementinfo'] = $elementinfo;
        $this->data['form'] = $mform->get_html();
    }

    /**
     * Export the data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['dropdown'] = $output->render($this->dropdown);
        return $this->data;
    }
}
