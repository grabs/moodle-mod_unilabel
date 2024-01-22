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

namespace mod_unilabel\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for mod_unilabel.
 *
 * This class originally is necessary for supplying custom completion rules for the activity.
 * Here it only serves to show the manual completion button on the course page regardleyy of the course's
 * showcompletionconditions setting.
 *
 * @package    mod_unilabel
 * @copyright  2024 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        return COMPLETION_UNKNOWN;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     * For this activity, there are no custom rules.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     * For this activity, there are no custom rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [];
    }

    /**
     * Show the manual completion or not regardless of the course's showcompletionconditions setting.
     *
     * @return bool
     */
    public function manual_completion_always_shown(): bool {
        return true;
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     * For this activity, there are no custom rules.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [];
    }
}
