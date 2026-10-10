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
 * unilabel type topic teaser.
 *
 * @package     unilabeltype_topicteaser
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
function unilabeltype_topicteaser_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    $itemid       = (int) array_shift($args);
    $relativepath = implode('/', $args);

    if (!unilabeltype_topicteaser_can_serve_section_file($context, $filearea, $itemid)) {
        return false;
    }

    // We switch the given component "unilabeltype_topicteaser" with the original "course".
    $fullpath = "/{$context->id}/course/$filearea/{$itemid}/$relativepath";

    $fs = get_file_storage();
    if ($file = $fs->get_file_by_hash(sha1($fullpath))) {
        if (!$file->is_directory()) {
            send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
        }
    }

    return false;
}

/**
 * Check whether a section summary file of a teased course may be sent to the current user.
 *
 * Only visible section files are served, and only if the user can view a topic teaser showing that course.
 *
 * @param  \context $context The course context of the teased course.
 * @param  string   $filearea
 * @param  int      $sectionid
 * @return bool
 */
function unilabeltype_topicteaser_can_serve_section_file($context, $filearea, $sectionid) {
    global $DB;

    if ($context->contextlevel != CONTEXT_COURSE || $filearea !== 'section') {
        return false;
    }
    $params = ['id' => $sectionid, 'course' => $context->instanceid, 'visible' => 1];
    if (!$DB->record_exists('course_sections', $params)) {
        return false;
    }

    $teasers = $DB->get_records('unilabeltype_topicteaser', ['course' => $context->instanceid], '', 'id, unilabelid');
    foreach ($teasers as $teaser) {
        $hostcm = get_coursemodule_from_instance('unilabel', $teaser->unilabelid, 0, false, IGNORE_MISSING);
        if (
            $hostcm && can_access_course(get_course($hostcm->course))
            && has_capability('mod/unilabel:view', context_module::instance($hostcm->id))
        ) {
            return true;
        }
    }

    return false;
}

/**
 * Fragment api hook to load the section content by ajax.
 *
 * @param  object|array      $args
 * @throws \moodle_exception
 * @return string            the html output of the section
 */
function unilabeltype_topicteaser_output_fragment_section($args) {
    global $DB;
    $args = (object) $args;
    /** @var \context $context */
    $context = $args->context;
    if (!has_capability('mod/unilabel:view', $context)) {
        return '';
    }
    if ($context->contextlevel != CONTEXT_COURSE) {
        throw new \moodle_exception('wrong context');
    }
    if (empty($args->sectionid)) {
        throw new \moodle_exception('missing sectionid');
    }
    if (!$section = $DB->get_record('course_sections', ['id' => $args->sectionid])) {
        throw new \moodle_exception('wrong sectionid');
    }

    return \unilabeltype_topicteaser\content_type::get_sections_content($context->instanceid, $section->section);
}
