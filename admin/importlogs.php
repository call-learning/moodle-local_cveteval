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
 * Import list or start new import Page
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_login();
require_capability('local/cveteval:manageimport', context_system::instance());
$importid = required_param('importid', PARAM_INT);
$restarturl = optional_param('restarturl', '/local/cveteval/pages/import.php', PARAM_RAW);
$continueurl = optional_param('continuenurl', '/local/cveteval/admin/importindex.php', PARAM_RAW);
$failed = optional_param('failed', false, PARAM_BOOL);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('import:logs', 'local_cveteval'));
$PAGE->set_heading(get_string('import:logs', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/admin/importlogs.php'));
/* @var core_renderer $OUTPUT */

if (!$failed) {
    $PAGE->set_button(
            $OUTPUT->single_button(new moodle_url($continueurl),
                    get_string('continue'))
    );
}
echo $OUTPUT->header();
if ($failed) {
    echo $OUTPUT->box_start('alert alert-primary');
    echo $OUTPUT->box(get_string('import:failed', 'local_cveteval'));
    echo $OUTPUT->single_button(new moodle_url('/local/cveteval/admin/cleanup.php', [
            'importid' => $importid,
            'type' => 'model',
            'returnurl' => $restarturl
    ]),
            get_string('import:cleanup', 'local_cveteval'));
    echo $OUTPUT->box_end();
}

$renderable = \local_cveteval\local\importer\import_log_utils::get_log_table($importid);
$renderer = $PAGE->get_renderer('local_cltools');
echo $renderer->render($renderable);

echo $OUTPUT->footer();
