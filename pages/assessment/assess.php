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
use local_cltools\local\filter\basic_filterset;
use local_cltools\local\filter\filter;
use local_cltools\local\filter\filterset;
use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\assessment\appraisals_student;
use local_cveteval\local\assessment\situations;

require_once(__DIR__ . '/../../../../config.php');
global $CFG, $OUTPUT, $PAGE, $USER;

use local_cveteval\local\assessment\situations_student;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\utils;

$evalplanid = required_param('evalplanid', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);
$currenttab = optional_param('tabname', 'thissituation', PARAM_ALPHA);
require_login();
if (utils::get_user_role_id($USER->id) != role_entity::ROLE_ASSESSOR_ID) {
    print_error('cannotaccess', 'local_cveteval');
}
$student = core_user::get_user($studentid);

$evalplan = new local_cveteval\local\persistent\planning\entity($evalplanid);
$situation = new local_cveteval\local\persistent\situation\entity($evalplan->get('clsituationid'));

$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('assess', 'local_cveteval', fullname($student))
    . ':'
    . get_string('assess', 'local_cveteval'));
$PAGE->set_heading(get_string('assess', 'local_cveteval'));
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
    get_string('assess', 'local_cveteval'),
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

$evaluationform->prepare_for_files();
if ($data = $evaluationform->get_data()) {
    try {
        $evaluationform->save_data();
        echo $OUTPUT->notification(get_string('saved', 'local_cveteval'), 'notifysuccess');
    } catch (\moodle_exception $e) {
        echo $OUTPUT->notification($e->getMessage(), 'notifyfailure');
    }
}

$evaluationform->display();

$tabs = array();
$tabs[] = new tabobject('thissituation',
    $currenturl->out(),
    get_string('thissituation', 'local_cveteval'));

//$currenturl->param('tabname', 'otherstudents');
//$tabs[] = new tabobject('otherstudents',
//    $currenturl->out(),
//    get_string('otherstudents', 'local_cveteval'));;

$currenturl->param('tabname', 'othersituations');
$tabs[] = new tabobject('othersituations',
    $currenturl->out(),
    get_string('othersituations', 'local_cveteval'));

echo $OUTPUT->tabtree($tabs, $currenttab);

$entitylist = null;

switch ($currenttab) {
    case "thissituation":
        $uniqueid = \html_writer::random_id('thisituationtable');
        $entitylist = new appraisals_student($uniqueid);
        $filterset = new basic_filterset(
            [
                'roletype' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
                'situationid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ],
                'studentid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ]
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID])]
        );
        $filterset->add_filter_from_params(
            'situationid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $situation->get('id')])]
        );
        $filterset->add_filter_from_params(
            'studentid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $studentid])]
        );
        $entitylist->set_extended_filterset($filterset);

        $renderer = $PAGE->get_renderer('local_cltools');
        /** @var entity_table_renderable entity table */
        $renderable = new entity_table_renderable($entitylist,['dataTree'=> true]);
        echo $renderer->render($renderable);

        //$renderable = new entity_table_renderable($entitylist);
        //$template = 'local_cveteval/assess_student_table';
        //$renderable = new entity_table_renderable($entitylist);
        //echo $OUTPUT->render_from_template($template, $renderable->export_for_template($OUTPUT));
        break;
    case "othersituations":
        $uniqueid = \html_writer::random_id('othersituation');
        $entitylist = new situations_student($uniqueid);
        $filterset = new basic_filterset(
            [
                'studentid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ]
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'studentid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $studentid])]
        );
        $entitylist->set_extended_filterset($filterset);
        $renderer = $PAGE->get_renderer('local_cltools');
        /** @var entity_table_renderable entity table */
        $renderable = new entity_table_renderable($entitylist);
        echo $renderer->render($renderable);
        break;
}

echo $OUTPUT->footer();