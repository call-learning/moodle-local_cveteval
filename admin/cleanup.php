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
 * Cleanup all tables
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\utils;

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");

$cleanuptype = required_param('type', PARAM_ALPHA);
admin_externalpage_setup('cvetevalcleanup' . $cleanuptype);
require_capability('local/cveteval:cleanupdata', context_system::instance());
$PAGE->set_title(get_string('cleanup:' . $cleanuptype, 'local_cveteval'));
$PAGE->set_heading(get_string('cleanup:' . $cleanuptype, 'local_cveteval'));
$confirm = optional_param('confirm', false, PARAM_BOOL);
$importid = optional_param('importid', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '/local/cveteval/admin/cleanup.php?type=' . $cleanuptype, PARAM_RAW);
$currenturl = new moodle_url('/local/cveteval/admin/cleanup.php', ['type' => $cleanuptype,
        'confirm' => 1,
        'importid' => $importid,
        'returnurl' => $returnurl
]);
$PAGE->set_url($currenturl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cleanup:' . $cleanuptype, 'local_cveteval'));

if (!$importid) {
    $histories = local_cveteval\local\persistent\history\entity::get_records();
    $selection = [];
    foreach (local_cveteval\local\persistent\history\entity::get_records() as $history) {
        $selection[$history->get('id')] = $history->get('idnumber');
    }
    sesskey();
    /* @var core_renderer $OUTPUT .*/
    $currenturl->remove_all_params();
    $currenturl->param('type', $cleanuptype);
    $currenturl->param('returnurl', $returnurl);
    echo $OUTPUT->single_select(
            $currenturl,
            'importid',
            $selection,
            '',
            array('' => 'choosedots'),
            null,
            ['label' => get_string('cleanup:selectimportid', 'local_cveteval')]
    );
} else {
    $currenthistory = new local_cveteval\local\persistent\history\entity($importid);
    /* @var core_renderer $OUTPUT .*/
    echo $OUTPUT->box(get_string('cleanup:details', 'local_cveteval', $currenthistory->to_record()),
            'generalboxalert alert-secondary');

    $currenthistory->get('idnumber');
    if ($confirm) {
        require_sesskey();
        $cleanupcb = 'cleanup_' . $cleanuptype;
        $progressbar = new progress_bar();
        $progressbar->create();

        utils::$cleanupcb($importid, $progressbar);
        /* @var core_renderer $OUTPUT .*/
        echo $OUTPUT->continue_button(
                new moodle_url($returnurl)
        );
    } else {
        sesskey();
        echo $OUTPUT->confirm(
                get_string('cleanup:confirm:' . $cleanuptype, 'local_cveteval'),
                $currenturl,
                new moodle_url('/admin/category.php', array('category' => 'cveteval'))
        );
    }
}

echo $OUTPUT->footer();
