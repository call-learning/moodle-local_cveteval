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

$evalplanid = required_param('evalplanid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$currenttab = optional_param('tabname', 'thissituation', PARAM_ALPHA);
require_login();
if (!roles::can_assess($USER->id)) {
    throw new moodle_exception('cannotaccess', 'local_cveteval');
}
$student = core_user::get_user($studentid);

$evalplan = new local_cveteval\local\persistent\planning\entity($evalplanid);
$situation = new local_cveteval\local\persistent\situation\entity($evalplan->get('clsituationid'));

$assesstitle = get_string('assess', 'local_cveteval', $situation->get('title'));
$assessfulltitle = get_string('assessment', 'local_cveteval')
    . ':'
    . $situation->get('title') . ':' . fullname($student);
$PAGE->set_context(context_system::instance());
$PAGE->set_title($assessfulltitle);
$PAGE->set_heading($assesstitle);
$PAGE->set_pagelayout('standard');
$currenturl = new moodle_url('/local/cveteval/pages/assessment/assess.php', array(
    'evalplanid' => $evalplanid,
    'studentid' => $studentid,
));
$PAGE->set_url($currenturl);
$situationnode = $PAGE->navigation->add(
    get_string('mysituations', 'local_cveteval'),
    new moodle_url('/local/cveteval/pages/assessment/mysituations.php'),
    navigation_node::TYPE_CONTAINER);
$studentnode = $situationnode->add(
    get_string('mystudents', 'local_cveteval'),
    new moodle_url('/local/cveteval/pages/assessment/mystudents.php', array(
        'situationid' => $situation->get('id'),
    )));
$currentnode = $studentnode->add(
    get_string('assessment', 'local_cveteval'),
    $currenturl);
$currentnode->make_active();

$currentfinaleval = null;
$currentfinaleval = local_cveteval\local\persistent\final_evaluation\entity::get_record(
    array('studentid' => $studentid, 'evalplanid' => $evalplanid));
if (!$currentfinaleval) {
    $currentfinaleval = new local_cveteval\local\persistent\final_evaluation\entity(0, (object) [
        'studentid' => $studentid,
        'evalplanid' => $evalplanid,
        'assessorid' => $USER->id
    ]
    );
}
$evaluationform = new local_cveteval\local\persistent\final_evaluation\form(null,
    [
        'tabname' => $currenttab,
        'persistent' => $currentfinaleval
    ], 'post', '', ['class' => 'd-flex flex-row ceveteval-eval-form']);

echo $OUTPUT->header();
/* @var $OUTPUT core_renderer .*/
echo $OUTPUT->heading($assessfulltitle, 3);
$evaluationform->prepare_for_files();
if ($data = $evaluationform->get_data()) {
    try {
        $evaluationform->save_data();
        echo $OUTPUT->notification(get_string('saved', 'local_cveteval'), 'notifysuccess');
    } catch (moodle_exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'notifyfailure');
    }
}

$studentuser = core_user::get_user($studentid);
$fullname = fullname($studentuser);
$userpicture = $OUTPUT->user_picture($studentuser);

echo html_writer::div(
        html_writer::div($userpicture)
        . html_writer::div($fullname));
$evaluationform->display();

$tabs = array();
$tabs[] = new tabobject('thissituation',
    $currenturl->out(),
    get_string('thissituation', 'local_cveteval'));

$currenturl->param('tabname', 'allsituations');
$tabs[] = new tabobject('allsituations',
    $currenturl->out(),
    get_string('allsituations', 'local_cveteval'));

echo $OUTPUT->tabtree($tabs, $currenttab);

$entitylist = null;

switch ($currenttab) {
    case "thissituation":
        $entitylist = assessment_utils::get_thissituation_list($studentid, $evalplan->get('id'));
        $renderer = $PAGE->get_renderer('local_cltools');
        /* @var entity_table_renderable entity table */
        $perpage = 0; // No pagination as it fails with dataTree.
        $renderable = new entity_table_renderable($entitylist, ['dataTree' => true], $perpage);
        echo $renderer->render($renderable);
        break;
    case "allsituations":
        $entitylist = assessment_utils::get_situations_for_student($studentid);
        $renderer = $PAGE->get_renderer('local_cltools');
        /* @var entity_table_renderable entity table */
        $renderable = new entity_table_renderable($entitylist);
        echo $renderer->render($renderable);
        break;
}

echo $OUTPUT->footer();
