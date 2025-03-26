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
 * Backup definition for this content type.
 * @package     unilabeltype_accordion
 * @copyright   2022 Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_unilabeltype_accordion_subplugin extends backup_subplugin {
    /**
     * Returns the nested structure of this content type.
     * @return \backup_subplugin_element
     */
    protected function define_unilabel_subplugin_structure() {
        // XML nodes declaration.
        $subplugin          = $this->get_subplugin_element();
        $subpluginwrapper   = new backup_nested_element($this->get_recommended_name());
        $subpluginaccordion = new backup_nested_element('unilabeltype_accordion', ['id'], ['showintro', 'type']);
        $subpluginsegment   = new backup_nested_element('unilabeltype_accordion_seg',
            ['id'],
            ['sortorder', 'heading', 'content', 'visible']
        );

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginaccordion);
        $subpluginaccordion->add_child($subpluginsegment);

        // Set source to populate the data.
        $subpluginaccordion->set_source_table('unilabeltype_accordion', ['unilabelid' => backup::VAR_ACTIVITYID]);
        $subpluginsegment->set_source_table('unilabeltype_accordion_seg', ['accordionid' => backup::VAR_PARENTID]);

        // Annotate files.
        $subpluginsegment->annotate_files('unilabeltype_accordion', 'heading', 'id');
        $subpluginsegment->annotate_files('unilabeltype_accordion', 'content', 'id');

        return $subplugin;
    }
}
