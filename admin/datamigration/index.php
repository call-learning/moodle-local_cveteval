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
 * Data migration wizard
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\datamigration\data_migration_controller;

require_once(__DIR__ . '../../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup('cvetevalmigration');
require_capability('local/cveteval:datamigration', context_system::instance());
$step = optional_param('step', 'init', PARAM_ALPHA);
$title = $step ?
        get_string('datamigrationstep', 'local_cveteval',
                get_string('dmcstep:' . $step, 'local_cveteval'))
        : get_string('datamigration', 'local_cveteval');

$PAGE->set_title($title);
$PAGE->set_heading($title);
$currenturl = new moodle_url('/local/cveteval/admin/datamigration/index.php', [
        'step' => $step
]);

$PAGE->set_url($currenturl);
$dmc = new data_migration_controller($step);
$renderable = $dmc->get_widget();
if ($form = $dmc->get_form($renderable)) {
    if ($data = $form->get_data()) {
        $form->execute_action($data);
    } else if ($form->is_cancelled()) {
        $form->execute_cancel();
    }
}
$dmc->prepare_page();

$renderer = $PAGE->get_renderer('local_cveteval');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('datamigration', 'local_cveteval'));

// Optionally execute related process or output information depending on the task.
echo $dmc->execute_process($renderer, $renderable, $form);

echo $OUTPUT->footer();
