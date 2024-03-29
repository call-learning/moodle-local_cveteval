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
 * Assessment Page
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\assessment\assessment_utils;
use local_cveteval\roles;

require_once(__DIR__ . '/../../../../config.php');
global $CFG, $OUTPUT, $PAGE, $USER;

require_login();
if (!roles::can_appraise($USER->id)) {
    throw new moodle_exception('cannotaccess', 'local_cveteval');
}
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('assessment', 'local_cveteval')
    . ':'
    . get_string('mysituations', 'local_cveteval'));
$PAGE->set_heading(get_string('assessment', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/pages/assessment/mysituations.php'));
$PAGE->set_pagelayout('standard');
$situationnode = $PAGE->navigation->add(
    get_string('mysituations', 'local_cveteval'),
    new moodle_url('/local/cveteval/pages/assessment/mysituations.php'),
    navigation_node::TYPE_CONTAINER);
$situationnode->make_active();
/* @var core_renderer $OUTPUT .*/
if (has_capability('local/cveteval:exportgrades', context_system::instance())) {
    $download = $OUTPUT->download_dataformat_selector(
        get_string('grades:export', 'local_cveteval'),
        $CFG->wwwroot . '/local/cveteval/pages/assessment/export.php');
    $PAGE->set_button(
        $download
    );
}
echo $OUTPUT->header();

echo $OUTPUT->box(get_string('mysituations:intro', 'local_cveteval'));
$entitylist = assessment_utils::get_mysituations_list();
$renderable = new entity_table_renderable($entitylist);

$renderer = $PAGE->get_renderer('local_cltools');
echo $renderer->render($renderable);

echo $OUTPUT->footer();
