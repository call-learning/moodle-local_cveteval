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
 * Lib for CompetVetEval
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\persistent\role\entity as role_entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Get plugin file
 *
 * @param $course
 * @param $cm
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 * @return false
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function local_cveteval_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }
    // Make sure the user is logged in and has access to the module
    // (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('local/cveteval:viewfiles', $context)) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_cveteval', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * Nothing for now
 */
function local_cveteval_enable_disable_plugin_callback() {
    $enabled = $CFG->enablecompetveteval ?? false;
    \local_cveteval\local\utils::setup_mobile_service($enabled);
}

/**
 * Extends navigation
 * @param global_navigation $nav
 * @throws coding_exception
 * @throws dml_exception
 */
function local_cveteval_extend_navigation(global_navigation $nav) {
    global $CFG, $USER;
    $enabled = !empty($CFG->enablecompetveteval) && $CFG->enablecompetveteval;
    if ($enabled) {
        if (\local_cveteval\local\utils::get_user_role_id($USER->id) == role_entity::ROLE_ASSESSOR_ID) {
            $node = navigation_node::create(
                get_string('assessment', 'local_cveteval'),
                new moodle_url('/local/cveteval/pages/assessment/mysituations.php'),
                navigation_node::TYPE_CUSTOM,
                'assessment',
                'key',
                new pix_icon('t/edit', get_string('edit'))
            );
            $node->showinflatnavigation = true;
            $nav->add_node($node);

        }
    }
}