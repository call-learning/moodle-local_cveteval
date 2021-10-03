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
use local_cveteval\local\importer\importid_manager;

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup('cvetevalimportindex');
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
    $delimiter = $formdata->delimiter;
    $encoding = $formdata->encoding;
    $importidmanager = new importid_manager($formdata->importidnumber, $formdata->importcomment);
    $importid = $importidmanager->get_importid();
    $importfailed = false;
    foreach (cveteval_import_form::get_files_to_upload_by_order() as $order => $filetype) {
        $fieldname = $filetype . 'file';
        $file = $form->save_temp_file($fieldname);
        $importclass = "\\local_cveteval\\local\\importer\\{$filetype}\\import_helper";
        if (!class_exists($importclass)) {
            throw new moodle_exception('importclassnotfound', 'local_cveteval', null, ' class:' . $importclass);
        }

        echo $OUTPUT->box(get_string('import:importing', 'local_cveteval',
            get_string('import:' . $filetype, 'local_cveteval')));
        $progressbar = new progress_bar();
        $progressbar->create();
        if ($file) {
            $fileinfo = $form->get_draft_file_from_elementname($fieldname);
            $importhelper = new $importclass($file, $importid, $fileinfo->get_filename(), $delimiter, $encoding, $progressbar);
            $importhelper->validate();
            $importhelper->import();
            /** @var \tool_importer\processor $processor */
            $processor = $importhelper->get_processor();
            $info = (object) [
                'rowcount' => $processor->get_row_imported_count(),
                'totalrows' => $processor->get_total_row_count(),
            ];
            $logs = $processor->get_logger()->get_logs(['level' => \tool_importer\local\log_levels::LEVEL_ERROR,
                'importid' => $importid]);
            if (!empty($logs)) {
                $importfailed = true;
            }
            echo $OUTPUT->box(get_string('import:imported', 'local_cveteval', $info));
            echo $OUTPUT->box($processor->get_displayable_stats());
        }
    }
    $manageurl = new moodle_url('/local/cveteval/admin/importindex.php');

    $continueurl = new moodle_url('/local/cveteval/admin/importlogs.php',
        array(
            'importid' => $importid,
            'failed' => $importfailed,
            'returnurl' => $manageurl->out(true)
        )
    );

    echo $OUTPUT->continue_button($continueurl);
    echo $OUTPUT->footer();
    exit;
}

$form->display();

echo $OUTPUT->footer();
