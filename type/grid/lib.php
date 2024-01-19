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
 * unilabel type grid.
 *
 * @package     unilabeltype_grid
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param mixed $course
 * @param mixed $cm
 * @param mixed $context
 * @param mixed $filearea
 * @param mixed $args
 * @param mixed $forcedownload
 */

/**
 * Send files provided by this plugin.
 *
 * @param  \stdClass $course
 * @param  \stdClass $cm
 * @param  \context  $context
 * @param  string    $filearea
 * @param  array     $args
 * @param  bool      $forcedownload
 * @return bool
 */
function unilabeltype_grid_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/unilabel:view', $context)) {
        return false;
    }

    if (($filearea !== 'image') && ($filearea !== 'image_mobile') && ($filearea !== 'content')) {
        return false;
    }

    $relativepath = implode('/', $args);
    $fullpath     = '/' . $context->id . '/unilabeltype_grid/' . $filearea . '/' . $relativepath;

    $fs = get_file_storage();
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
        if (!$file->is_directory()) {
            send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
        }
    }

    return false;
}

/**
 * Get a html fragment.
 *
 * @param  mixed  $args an array or object with context and parameters needed to get the data
 * @return string The html fragment we want to use by ajax
 */
function unilabeltype_grid_output_fragment_get_html($args) {
    global $CFG, $PAGE, $FULLME, $OUTPUT;
// return '<h2>Das ist ein Text</h2>';
    // $editelement = new \unilabeltype_grid\output\edit_element();
    // return $OUTPUT->render($editelement);
    // require_once($CFG->libdir . '/formslib.php');
    // require_once($CFG->libdir . '/form/filemanager.php');

    $PAGE->set_url(new \moodle_url($FULLME));
    $PAGE->set_context(\context_system::instance());

    $formid = $args['formid'];
    $context = \context::instance_by_id($args['contextid']);
    $course = get_course($args['courseid']);
    $prefix = $args['prefix'];
    $repeatindex = intval($args['repeatindex']);

    $editelement = new \unilabeltype_grid\output\edit_element(
        $formid,
        $context,
        $course,
        $prefix,
        $repeatindex
    );

    return $OUTPUT->render($editelement);

    // $attributes = [];
    // $attributes['id'] = '222222';
    // $attributes['name'] = 'blabblblblblb';

    // $element = new \MoodleQuickForm_filemanager('bla', 'bla-label', $attributes);
    // $html = $element->toHtml();

    // return $html;
}
