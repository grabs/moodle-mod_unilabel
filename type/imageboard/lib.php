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

/**
 * Send files provided by this plugin
 *
 * @param \stdClass $course
 * @param \stdClass $cm
 * @param \context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool
 */
function unilabeltype_imageboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/unilabel:view', $context)) {
        return false;
    }

    if (($filearea !== 'backgroundimage') && ($filearea !== 'image')) {
        return false;
    }

    $relativepath = implode('/', $args);
    $fullpath = '/'.$context->id.'/unilabeltype_imageboard/'.$filearea.'/'.$relativepath;

    $fs = get_file_storage();
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
        if (!$file->is_directory()) {
            send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
        }
    }
    return false;
}
