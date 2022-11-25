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

    /**
     * As we have a test that does write into the DB, we need to setup and tear down each time
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        test_utils::setup_from_shortsample();
        $user1 = core_user::get_user_by_username('etu1');
        $user2 = core_user::get_user_by_username('etu2');
        $obs1 = core_user::get_user_by_username('obs1');
        $currentgrid = grid_entity::get_record(['idnumber' => 'GRID01']);
        test_utils::create_appraisal_for_students($user1->id, false, false, $obs1->id, $currentgrid->get('id'));
        test_utils::create_appraisal_for_students($user2->id, false, false, $obs1->id, $currentgrid->get('id'));
    }


    /**
     * Test an API function
     */
    public function test_get_appraisal() {
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
        $this->assertCount(4, $appraisals); // 2 appraisal per eval plan for 2 users.
        $user2 = core_user::get_user_by_username('obs2'); // Now as obs2.
        $this->setUser($user2);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(12, $appraisals); // 2 appraisal per students and eval plan (3).

    }

    /**
     * Test an API function
     */
    public function test_get_appraisal_additionalnotinplan() {
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
     */
    public function test_get_appraisal_crit() {
        global $DB;
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
        $this->assertCount(4 * 40, $appraisalscrit); // 2 situations with students for this user in his planning.
    }

}
