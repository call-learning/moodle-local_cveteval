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

namespace local_cveteval\local\assessment;

use advanced_testcase;
use core_user;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\evaluation_grid\entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use moodle_url;
use local_cveteval\test\test_utils;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/externallib.php');

/**
 * API tests
 *
 * @package     local_cveteval
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessment_test extends advanced_testcase {
    /**
     * Setup before tests.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        test_utils::setup_from_shortsample();
        $student = core_user::get_user_by_username('etu1');
        $obs1 = core_user::get_user_by_username('obs1');
        $currentgrid = entity::get_record(['idnumber' => 'GRID01']);
        test_utils::create_appraisal_for_students($student->id, null, false, $obs1->id, $currentgrid->get('id'));
    }

    /**
     * Test
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_mysituations_list
     */
    public function test_list_situation() {
        $obs1 = core_user::get_user_by_username('obs1');
        $this->setUser($obs1);
        $entitylist = assessment_utils::get_mysituations_list();
        $rows = $entitylist->get_rows(100);
        $this->assertCount(1, $rows);
        $this->assertEquals("TMG", $rows[0]->idnumber);
    }

    /**
     * Test listing my students
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_mysituations_list
     */
    public function test_list_mystudents() {
        $resp1 = core_user::get_user_by_username('resp1');
        $this->setUser($resp1);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $entitylist = assessment_utils::get_mystudents_list($tmgsituation->get('id'));
        $rows = $entitylist->get_rows(100);
        $this->assertCount(10, $rows);
        $expected = [
            [
                'studentfullname' => "Adéla Veselá",
                'groupname' => "Groupe A"
            ],
            [
                'studentfullname' => "Anna Horáková",
                'groupname' => "Groupe A"
            ],
            [
                'studentfullname' => "Dan Martin",
                'groupname' => "Groupe B"
            ],
        ];
        $studentlist = array_map(
            function($s) {
                return ['studentfullname' => $s->studentfullname, 'groupname' => $s->groupname];
            },
            $rows
        );
        foreach ($expected as $e) {
            $this->assertContains($e, $studentlist);
        }
        $this->assertTrue($rows[2]->planid != $rows[1]->planid);
    }

    /**
     * Test listing appraisal for a student
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_mysituations_list
     */
    public function test_list_appraisal_students() {
        $obs1 = core_user::get_user_by_username('obs1');
        $student = core_user::get_user_by_username('etu1');
        $this->setUser($obs1);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $evalplans = planning_entity::get_records(array('clsituationid' => $tmgsituation->get('id')));
        $evalplan = reset($evalplans);
        $entitylist = assessment_utils::get_thissituation_list($student->id, $evalplan->get('id'));
        $rows = $entitylist->get_rows(100);
        $this->assertCount(7, $rows);
        $this->assertEquals("Savoir être", $rows[0]->criterionname);
        $this->assertEquals("Respect des horaires de travail", $rows[0]->_children[0]->criterionname);
    }

    /**
     * Test getting list of assessment criteria
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_mysituations_list
     */
    public function test_list_assessment_criteria() {
        $obs1 = core_user::get_user_by_username('obs1');
        $student = core_user::get_user_by_username('etu1');
        $this->setUser($obs1);
        $tmgsituation = situation_entity::get_record(array('idnumber' => 'TMG'));
        $evalplans = planning_entity::get_records(array('clsituationid' => $tmgsituation->get('id')));
        $evalplan = reset($evalplans);
        $appraisal = appraisal_entity::get_record(array('studentid' => $student->id, 'appraiserid' => $obs1->id,
            'evalplanid' => $evalplan->get('id')));
        $entitylist = assessment_utils::get_assessmentcriteria_list($appraisal->get('id'));
        $rows = $entitylist->get_rows(100);
        $this->assertCount(7, $rows);
        $this->assertCount(5, $rows[0]->_children);
    }
}
