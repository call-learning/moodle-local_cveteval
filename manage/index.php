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
 * Main page for all editions
 *
 * Routing is made through the action parameter.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
global $CFG, $OUTPUT, $PAGE;
$importid = required_param('importid', PARAM_INT);
require_capability('local/cveteval:manageentities', context_system::instance());

$PAGE->set_url('/local/cveteval/manage/index.php', ['importid' => $importid]);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
\local_cveteval\utils::setup_entity_management_page_navigation($importid);

$files = scandir(__DIR__);
$folders = array_filter($files, function($file) {
    return is_dir(__DIR__ . "/$file") && !in_array($file, ['.', '..']);
});
foreach ($folders as $folder) {
    $basenamefolder = basename($folder);
    if (file_exists($folder . '/index.php')
            && class_exists('\\local_cveteval\\local\\persistent\\' . $basenamefolder . '\\entity')) {
        $innerlinks[$basenamefolder] = "{$CFG->wwwroot}/local/cveteval/manage/$folder/index.php";
    }
}

$innerlinkshtml = [];
foreach ($innerlinks as $name => $linkurl) {
    $linkhtml = html_writer::span(get_string("$name:entity", 'local_cveteval'));
    $linkhtml .= html_writer::link(
            new moodle_url($linkurl, ['importid' => $importid]), get_string('edit'), array('class' => 'm-1 btn btn-secondary'));
    $innerlinkshtml[] = $linkhtml;
}
echo $OUTPUT->header();
echo html_writer::alist($innerlinkshtml);
echo $OUTPUT->footer();
