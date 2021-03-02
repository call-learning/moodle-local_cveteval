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

use local_cltools\local\crud\entity_utils;
use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\assessment\assessment_planning;

require_once(__DIR__ . '/../../config.php');
global $CFG, $OUTPUT, $PAGE;
$situationid = optional_param('situationid', 0, PARAM_INT);
require_login();;
$PAGE->set_context(\context_system::instance());
$PAGE->set_title( get_string('assessment', 'local_cveteval'));
$PAGE->set_heading( get_string('assessment', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/assess.php'));
echo $OUTPUT->header();
if (empty($situationid)) {
    // Show planning list
    // Here the form is just the filter form.
    $uniqueid = \html_writer::random_id('assessmenttable');
    $entitylist = new assessment_planning($uniqueid);
    $renderable = new entity_table_renderable($entitylist);

    $renderer = $PAGE->get_renderer('local_cltools');
    /** @var entity_table_renderable entity table */
    echo $renderer->render($renderable);

} else {
    // Show situation and student list
}
echo $OUTPUT->footer();