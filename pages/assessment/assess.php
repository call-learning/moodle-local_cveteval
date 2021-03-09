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

use local_cveteval\local\assessment\mystudents;
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

$evalplan =  new local_cveteval\local\persistent\planning\entity($evalplanid);
$situation = new local_cveteval\local\persistent\situation\entity($evalplan->get('clsituationid'));

$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('assess', 'local_cveteval', fullname($student))
    .':'
    .get_string('assess', 'local_cveteval'));
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
    new moodle_url('/local/cveteval/pages/assessment/mystudents.php',  array(
        'situationid' => $situation->get('id'),
    )));
$currentnode = $studentnode->add(
    get_string('assess', 'local_cveteval'),
    $currenturl);
$currentnode->make_active();

$currentfinaleval = null;
$currentfinaleval = local_cveteval\local\persistent\final_evaluation\entity::get_record(
    array('studentid'=> $studentid, 'evalplanid'=>$evalplanid));
if (!$currentfinaleval)  {
    $currentfinaleval = null;
}
$evaluationform =  new local_cveteval\local\persistent\final_evaluation\form(null,
    [
        'studentid'=> $studentid,
        'evalplanid' => $evalplanid,
        'persistent' => $currentfinaleval
    ], 'post', '', ['class'=>'d-flex flex-row']);

echo $OUTPUT->header();

$evaluationform->prepare_for_files();
if ($data = $evaluationform->get_data()) {
    try {
        $evaluationform->save_submitted_files($data);
        $currentfinaleval->from_record($data);
        $currentfinaleval->update();

        echo $this->renderer->notification(get_string('saved'), 'notifysuccess');
        redirect(
            new moodle_url($this->persistentnavigation->get_view_url(), ['id' => $entity->get('id')]),
            $this->get_action_event_description(),
            null,
            $messagetype = \core\output\notification::NOTIFY_SUCCESS);
    } catch (\moodle_exception $e) {
        echo $this->renderer->notification($e->getMessage(), 'notifyfailure');
    }
}

$evaluationform->display();

$tabs = array();
$tabs[] = new tabobject('thissituation',
    $currenturl->out(),
    get_string('thissituation', 'local_cveteval'));

$currenturl->param('tabname', 'otherstudents');
$tabs[] = new tabobject('otherstudents',
    $currenturl->out(),
    get_string('otherstudents', 'local_cveteval'));;

$currenturl->param('tabname', 'otherrotations');
$tabs[] = new tabobject('otherrotations',
    $currenturl->out(),
    get_string('othersituations', 'local_cveteval'));

echo $OUTPUT->tabtree($tabs, $currenttab);

$uniqueid = \html_writer::random_id('situationtable');
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
    [json_encode((object) ['direction' => '=', 'value' =>  role_entity::ROLE_ASSESSOR_ID])]
);
$filterset->add_filter_from_params(
    'situationid', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object)['direction' => '=', 'value'=>$situation->get('id')])]
);
$filterset->add_filter_from_params(
    'studentid', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object)['direction' => '=', 'value'=>$studentid])]
);
$entitylist->set_extended_filterset($filterset);

$renderable = new entity_table_renderable($entitylist);

echo $OUTPUT->render_from_template('local_cveteval/assess_student_table',
    $renderable->export_for_template($OUTPUT));

echo $OUTPUT->footer();