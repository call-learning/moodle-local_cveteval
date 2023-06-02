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
 * API tests
 *
 * @package     local_cveteval
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use core_user;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criteria_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as grid_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\test\test_utils;

global $CFG;

require_once($CFG->libdir . '/externallib.php');

/**
 * API tests
 *
 * @package     local_cveteval
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_appraisals_test extends advanced_testcase {

    private $appraisals = [];

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        test_utils::setup_from_shortsample();
    }

    /**
     * Test an API function
     * @cover \local_cveteval\local\external\appraisal::get
     */
    public function test_get_appraisal() {
        $this->setup_appraisals();
        $this->setAdminUser();
        $user1 = core_user::get_user_by_username('etu1');
        $appraisals = appraisal::get();
        $this->assertEmpty($appraisals);

        // Now, I am user 1, I should only get appraisal involving me (either as a student or appraiser).
        $this->setUser($user1);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(6, $appraisals); // 6 situations for this user in his planning.

        $user2 = core_user::get_user_by_username('obs1'); // Now as obs1.
        $this->setUser($user2);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(6, $appraisals); // 3 appraisal per eval plan for 2 users.

        $user2 = core_user::get_user_by_username('obs2'); // Now as obs2.
        $this->setUser($user2);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(18, $appraisals); // 3 appraisal per students and eval plan (3) and 3 situations.

    }

    /**
     * Setup appraisal for tests
     *
     * @return void
     */
    private function setup_appraisals() {
        $user1 = core_user::get_user_by_username('etu1');
        $user2 = core_user::get_user_by_username('etu2');
        $currentgrid = grid_entity::get_record(['idnumber' => 'GRID01']);
        test_utils::create_appraisals_for_students($user1->id, false, false, null, $currentgrid->get('id'));
        test_utils::create_appraisals_for_students($user2->id, false, false, null, $currentgrid->get('id'));
        test_utils::create_appraisals_for_students($user2->id, false, false, 0, $currentgrid->get('id'));
    }

    /**
     * Test an API function
     *
     * @covers \local_cveteval\local\external\appraisal::get
     */
    public function test_get_appraisal_additionalnotinplan() {
        $this->setup_appraisals();
        $this->setAdminUser();
        $user1 = core_user::get_user_by_username('etu1');
        $appraisals = appraisal::get();
        $this->assertEmpty($appraisals);
        // Now, I am user 1, I should only get appraisal involving me (either as a student or appraiser)
        // Create an appraisal that is not in any of the user's plan.
        $appraisal = new appraisal_entity(0, (object) [
            'studentid' => $user1->id,
            'appraiserid' => 1,
            'evalplanid' => 9999,
            'context' => 'Should not appear in the list',
            'contextformat' => FORMAT_HTML,
            'comment' => 'Should not appear in the list',
            'commentformat' => FORMAT_HTML
        ]);
        $appraisal->create();
        $appraisal->save();
        $this->setUser($user1);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(6, $appraisals); // 6 situations for this user in his planning.
        $allcomments = array_map(function($appr) {
            return $appr->context;
        }, $appraisals);
        $this->assertNotContains('Should not appear in the list', $allcomments);
    }

    /**
     * Test an API function
     *
     * @covers \local_cveteval\local\external\appr_crit::get
     */
    public function test_get_appraisal_crit() {
        global $DB;
        $this->setup_appraisals();
        $this->setAdminUser();
        $user1 = core_user::get_user_by_username('etu1');
        // This should retrieve an empty list as we are currently an admin.
        $appraisalscrit = appr_crit::get();
        $this->assertEmpty($appraisalscrit);
        // Now, I am user 1, I should only get appraisal involving me (either as a student or appraiser).
        $this->setUser($user1);
        $appraisalscrit = appr_crit::get();
        $this->assertNotEmpty($appraisalscrit, json_encode($DB->get_records(appraisal_criteria_entity::TABLE), true));
        // Group A: 6 appraisals, 40 criteria => 240 criteria.
        $this->assertCount(6 * 40, $appraisalscrit); // 6 situations for this user in his planning.
        // Now I am an appraiser, I should see my appraisals.
        $obs1 = core_user::get_user_by_username('obs1');
        $this->setUser($obs1);
        $appraisalscrit = appr_crit::get();
        $this->assertNotEmpty($appraisalscrit);
        // 2 situations (TMG in Group A), 2 students, 40 criteria => 4*40 criteria.
        $this->assertCount(6 * 40, $appraisalscrit); // 2 situations with students for this user in his planning.
    }

    /**
     * Test an API function
     *
     * @covers \local_cveteval\local\external\appr_crit::delete
     */
    public function test_delete_appraisal() {
        $this->setAdminUser();
        $etu1 = core_user::get_user_by_username('etu1');
        $etu2 = core_user::get_user_by_username('etu2');
        $obs1 = core_user::get_user_by_username('obs1');
        $evalplans = planning_entity::get_records();
        $evalplan = end($evalplans);
        $situationid = $evalplan->get('clsituationid');

        // First check we can delete an appraisal when I am a student in this appraisal BUT this has not yet been assigned.
        $appraisal = test_utils::create_appraisal($etu1->id, 0, $evalplan->get('id'), $situationid);
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
        $this->setUser($appraisal->get('studentid'));
        $result = appraisal::delete($appraisal->get('id'));
        $this->assertEmpty($result); // Yes we can.
        $this->assertEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));

        // Then check I cannot delete an appraisal when it has already been assigned.
        $appraisal = test_utils::create_appraisal($etu1->id, $obs1->id, $evalplan->get('id'), $situationid);
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
        $this->setUser($appraisal->get('studentid'));
        $result = appraisal::delete($appraisal->get('id'));
        $this->assertNotEmpty($result); // No we cannot.
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));

        // Then check we can delete an appraisal when I am an appraiser in this appraisal.
        $appraisal = test_utils::create_appraisal($etu1->id, $obs1->id, $evalplan->get('id'), $situationid);
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
        $this->setUser($appraisal->get('appraiserid'));
        $result = appraisal::delete($appraisal->get('id'));
        $this->assertEmpty($result); // Yes we can.
        $this->assertEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));

        // Third check we can delete an appraisal when I am an admin.
        $appraisal = test_utils::create_appraisal($etu1->id, $obs1->id, $evalplan->get('id'), $situationid);
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
        $this->setAdminUser();
        $result = appraisal::delete($appraisal->get('id'));
        $this->assertEmpty($result); // Yes we can.
        $this->assertEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));

        // Now check that we cannot if we are neither of theses.
        $appraisal = test_utils::create_appraisal($etu1->id, $obs1->id, $evalplan->get('id'), $situationid);
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
        $this->setUser($etu2->id);
        $result = appraisal::delete($appraisal->get('id'));
        $this->assertNotEmpty($result);
        $this->assertEquals('cannotdeleteappraisal', $result[0]['warningcode']); // No we can't.
        $this->assertNotEquals(0, appraisal_entity::count_records(['id' => $appraisal->get('id')]));
        $this->assertNotEquals(0, appraisal_criteria_entity::count_records(['appraisalid' => $appraisal->get('id')]));
    }

}
