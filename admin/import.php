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
 * Import elements
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\forms\cveteval_import_form;
use local_cveteval\local\utils;

define('NO_OUTPUT_BUFFERING', true);
global $CFG;
require_once(__DIR__ . '../../../../config.php');
require_once($CFG->libdir . "/adminlib.php");
global $CFG, $OUTPUT, $PAGE;
admin_externalpage_setup('cvetevalimport');
$PAGE->set_title(get_string('import', 'local_cveteval'));
$PAGE->set_heading(get_string('import', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/pages/import.php'));
$PAGE->set_cacheable(false);    // Progress bar is used here.

$form = new cveteval_import_form();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('import:heading:process', 'local_cveteval'));

/* @var core_renderer $OUTPUT */
if ($formdata = $form->get_data()) {

    global $DB;
    // Just in case.
    require_sesskey();
    $files = [];
    foreach (cveteval_import_form::get_files_to_upload() as $filetype => $settings) {
        $fieldtype = $filetype . 'file';
        $files[$filetype] = $form->save_temp_file($fieldtype);
    }
    $filesbyorder = array_map(function($ft) {
        return $ft['order'];
    },
        cveteval_import_form::get_files_to_upload());
    $filesbyorder = array_flip($filesbyorder);
    ksort($filesbyorder);
    $delimiter = $formdata->delimiter;
    $encoding = $formdata->encoding;
    $cleanupbefore = $formdata->cleanupbefore;

    $importid = utils::get_next_importid();
    foreach ($filesbyorder as $order => $filetype) {
        $importclass = "\\local_cveteval\\local\\importer\\{$filetype}\\import_helper";
        if (!class_exists($importclass)) {
            print_error(get_string('importclassnotfound', 'local_cveteval') . ' class:' . $importclass);
        }
        $fileinput = $files[$filetype];
        echo $OUTPUT->box(get_string('import:importing', 'local_cveteval',
            get_string('import:'.$filetype, 'local_cveteval')));
        $progressbar = new progress_bar();
        $progressbar->create();
        if ($fileinput) {
            $importhelper =  new $importclass($files[$filetype], $importid, $delimiter, $encoding, $progressbar);

            if (!empty($cleanupbefore)) {
                $importhelper->cleanup();
            }
            $importhelper->import();
            $info = (object) [
                'rowcount'=>$importhelper->get_row_imported_count(),
                'totalrows'=> $importhelper->get_total_row_count()
            ];
            echo $OUTPUT->box(get_string('import:imported', 'local_cveteval',$info));
        }
    }
    $manageurl = new moodle_url('/local/cveteval/pages/index.php');
    $continueurl = new moodle_url('/local/cveteval/admin/importlogs.php',
        array(
            'importid' => $importid,
            'returnurl' => $manageurl->out(true)
        )
    );

    echo $OUTPUT->continue_button($continueurl);
    echo $OUTPUT->footer();
    exit;
}

$form->display();

echo $OUTPUT->footer();