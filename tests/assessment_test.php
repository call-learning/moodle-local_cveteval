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
 * Assessment query/routine tests
 *
 * @package     local_cveteval
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cltools;

use local_cveteval\local\assessment\assessment_utils;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use \local_cveteval\local\persistent\situation\entity as situation_entity;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * API tests
 *
 * @package     local_cltools
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cveteval_assessment_testcase extends \advanced_testcase {

    public function setUp() {
        global $CFG;
        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/local/cveteval/tests/helpers.php');
        $basepath = $CFG->dirroot . '/local/cveteval/tests/fixtures/';
        $data = [
            'users' => $CFG->dirroot . '/local/cveteval/tests/fixtures/ShortSample_Users.csv',
            'cveteval' => [
                'evaluation_grid' => "{$basepath}/Sample_Evalgrid.csv",
                'situation' => "{$basepath}/ShortSample_Situations.csv",
                'planning' => "{$basepath}/ShortSample_Planning.csv",
                'grouping' => "{$basepath}/ShortSample_Grouping.csv"
            ]
        ];
        import_sample_users($data['users']);
        import_sample_planning($data['cveteval'], true);
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
    }

    /**
     * Test
     */
    public function test_list_situation() {
        $this->resetAfterTest();
        $obs1 = \core_user::get_user_by_username('obs1');
        $this->setUser($obs1);
        $entitylist = assessment_utils::get_mysituations_list($obs1->id);
        $entitylist->define_baseurl(new \moodle_url('/'));
        $rows = $entitylist->retrieve_raw_data(100);
        $this->assertCount(1, $rows);
        $this->assertEquals($rows[0]->idnumber, "TMG");
    }

    /**
     * Test listing my students
     */
    public function test_list_mystudents() {
        $this->resetAfterTest();
        $obs1 = \core_user::get_user_by_username('obs1');
        $this->setUser($obs1);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $entitylist = assessment_utils::get_mystudents_list($obs1->id, $tmgsituation->get('id'));
        $entitylist->define_baseurl(new \moodle_url('/'));
        $rows = $entitylist->retrieve_raw_data(100);
        $this->assertCount(10, $rows);
        $this->assertEquals($rows[0]->studentfullname, "Adéla Veselá");
        $this->assertEquals($rows[0]->groupname, "Groupe A");
        $this->assertEquals($rows[1]->studentfullname, "Adéla Veselá");
        $this->assertEquals($rows[1]->groupname, "Groupe A");
        $this->assertTrue($rows[0]->planid != $rows[1]->planid);
        $this->assertEquals($rows[2]->studentfullname, "Anna Horáková");
        $this->assertEquals($rows[2]->groupname, "Groupe A");
    }

    /**
     * Test listing appraisal for a student
     */
    public function test_list_appraisal_students() {
        $this->resetAfterTest();
        $obs1 = \core_user::get_user_by_username('obs1');
        $student = \core_user::get_user_by_username('etu1');
        $this->setUser($obs1);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $evalplans = planning_entity::get_records(array('clsituationid' => $tmgsituation->get('id')));
        $evalplan = reset($evalplans);
        $entitylist = assessment_utils::get_thissituation_list($student->id, $evalplan->get('id'));
        $entitylist->define_baseurl(new \moodle_url('/'));
        $rows = $entitylist->retrieve_raw_data(100);
        $this->assertCount(7, $rows);
        $this->assertEquals($rows[0]->criterionname, "Savoir être");
        $this->assertEquals($rows[0]->_children[0]->criterionname, "Respect des horaires de travail");
    }

    public function test_list_assessment_criteria() {
        $this->resetAfterTest();
        $obs1 = \core_user::get_user_by_username('obs1');
        $student = \core_user::get_user_by_username('etu1');
        $this->setUser($obs1);
        create_appraisal_for_students($student->id, null, false, $obs1->id);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $evalplans = planning_entity::get_records(array('clsituationid' => $tmgsituation->get('id')));
        $evalplan = reset($evalplans);
        $appraisal = appraisal_entity::get_record(array('studentid' => $student->id, 'appraiserid' => $obs1->id,
            'evalplanid' => $evalplan->get('id')));
        $entitylist = assessment_utils::get_assessmentcriteria_list($appraisal->get('id'));
        $entitylist->define_baseurl(new \moodle_url('/'));
        $rows = $entitylist->retrieve_raw_data(100);
        $this->assertCount(7, $rows);
        $this->assertCount(5, $rows[0]->_children);
    }
}