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
 * History of import management
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\download_helper;

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");
admin_externalpage_setup('cvetevalimportindex');
require_capability('local/cveteval:exportall', context_system::instance());

$importid = required_param('importid', PARAM_INT);
$dataformat = optional_param('dataformat', '', PARAM_TEXT);

$returnurl = optional_param('returnurl', $CFG->wwwroot . '/local/cveteval/admin/importindex.php', PARAM_RAW);
$downloadtype = optional_param('downloadtype', 'model', PARAM_ALPHA);
$type = optional_param('type', '', PARAM_ALPHANUMEXT);

$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('import:download', 'local_cveteval'));
$PAGE->set_heading(get_string('import:download', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/admin/export.php'));

if (!empty($dataformat) && !empty($importid) && !empty($type)) {
    $downloadcb = "download_{$downloadtype}_{$type}";
    download_helper::$downloadcb($importid, $dataformat);
    die();
}
echo $OUTPUT->header();
$PAGE->set_button(
        $OUTPUT->single_button(new moodle_url($returnurl),
                get_string('continue'))
);
sesskey();
echo $OUTPUT->heading(get_string('download:model', 'local_cveteval'));

foreach (['situation', 'planning', 'group', 'evaluation_grid'] as $filetype) {
    echo $OUTPUT->download_dataformat_selector(
            get_string('import:downloadfile', 'local_cveteval', get_string($filetype . ':entity', 'local_cveteval')),
            $CFG->wwwroot . '/local/cveteval/admin/export.php',
            'dataformat',
            ['importid' => $importid, 'type' => $filetype, 'downloadtype' => 'model']
    );
}
echo $OUTPUT->heading(get_string('download:userdata', 'local_cveteval'));

foreach (['appraisal', 'final_evaluation'] as $filetype) {
    echo $OUTPUT->download_dataformat_selector(
            get_string('import:downloadfile', 'local_cveteval', get_string($filetype . ':entity', 'local_cveteval')),
            $CFG->wwwroot . '/local/cveteval/admin/export.php',
            'dataformat',
            ['importid' => $importid, 'type' => $filetype, 'downloadtype' => 'userdata']
    );
}

echo $OUTPUT->footer();
