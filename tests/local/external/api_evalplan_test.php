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
use local_cveteval\local\persistent\role\entity as role_entity;
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
class api_evalplan_test extends advanced_testcase {


    /**
     * setUp all and do not reset between the tests (as the model should not change)
     * This speeds up the test greatly.
     */
    public static function setUpBeforeClass(): void {
        test_utils::setup_from_shortsample();
    }

    /**
     * Reset all data
     */
    public static function tearDownAfterClass(): void {
        parent::tearDownAfterClass();
        self::resetAllData();
    }

    /**
     * setUp
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(false);
    }

    /**
     * Test an API function
     */
    public function test_get_get_evalplan_no_user() {
        // First, no user logged in.
        $this->setAdminUser();
        $evalplan = evalplan::get();
        $this->assertEmpty($evalplan);
    }

    /**
     * Test an API function
     */
    public function test_get_get_evalplan_student() {
        global $DB;
        $this->setAdminUser();
        // First, no user logged in.
        $user1 = core_user::get_user_by_username('etu1');
        // Now, I am user 1, I should only get evalplans involving me (either as a student or appraiser).
        $this->setUser($user1);
        $evalplan = evalplan::get();
        $this->assertNotEmpty($evalplan);
        $this->assertCount(6, $evalplan);
        $allgroupidmatch =
            $DB->get_records_menu('local_cveteval_group', null, '', 'id,name');
        $allclsituation =
            $DB->get_records_menu('local_cveteval_clsituation', null, '', 'id,title');
        // We retrieve all situations here.

        $this->assertEquals(
            array_values(array_filter($this->get_all_evalplans(), function($plan) {
                return $plan->groupid == 'Groupe A';
            })),
            array_values(array_map(function($s) use ($allgroupidmatch, $allclsituation) {
                return (object) [
                    'groupid' => $allgroupidmatch[$s->groupid],
                    'clsituationid' => $allclsituation[$s->clsituationid],
                    'starttime' => strftime('%d/%m/%Y', $s->starttime),
                    'endtime' => strftime('%d/%m/%Y', $s->endtime),
                ];
            }, $evalplan)));

    }

    /**
     * All eval plans
     */
    protected function get_all_evalplans() {
        return [
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Consultations de médecine générale',
                'starttime' => '24/05/2021',
                'endtime' => '30/05/2021'
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Urgences-Soins intensifs',
                'starttime' => '24/05/2021',
                'endtime' => '30/05/2021'
            ],
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Médecine interne',
                'starttime' => '31/05/2021',
                'endtime' => '06/06/2021'
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Consultations de médecine générale',
                'starttime' => '31/05/2021',
                'endtime' => '06/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Urgences-Soins intensifs',
                'starttime' => '07/06/2021',
                'endtime' => '13/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Médecine interne',
                'starttime' => '31/05/2021',
                'endtime' => '06/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Consultations de médecine générale',
                'starttime' => '14/06/2021',
                'endtime' => '20/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Urgences-Soins intensifs',
                'starttime' => '03/05/2021',
                'endtime' => '10/05/2021',
            ],
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Médecine interne',
                'starttime' => '21/06/2021',
                'endtime' => '27/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Consultations de médecine générale',
                'starttime' => '21/06/2021',
                'endtime' => '27/06/2021',
            ],
            (object) [
                'groupid' => 'Groupe A',
                'clsituationid' => 'Urgences-Soins intensifs',
                'starttime' => '28/06/2021',
                'endtime' => '04/07/2021',
            ],
            (object) [
                'groupid' => 'Groupe B',
                'clsituationid' => 'Médecine interne',
                'starttime' => '17/05/2021',
                'endtime' => '24/05/2021',
            ]
        ];
    }

    /**
     * Test an API function
     */
    public function test_get_get_evalplan_observer() {
        global $DB;
        // First, no user logged in.
        $user1 = core_user::get_user_by_username('obs1'); // Obs1 is only in one situation.
        // Now, I am obs2, I should only get evalplans involving me (either as a student or appraiser).
        $this->setUser($user1);
        $evalplan = evalplan::get();
        $this->assertNotEmpty($evalplan);
        $this->assertCount(4, $evalplan);
        $allgroupidmatch =
            $DB->get_records_menu('local_cveteval_group', null, '', 'id,name');
        $allclsituation =
            $DB->get_records_menu('local_cveteval_clsituation', null, '', 'id,title');
        // We retrieve all situations here.

        $this->assertEquals(
            array_values(array_filter($this->get_all_evalplans(), function($plan) {
                return $plan->clsituationid == 'Consultations de médecine générale';
            })),
            array_values(array_map(function($s) use ($allgroupidmatch, $allclsituation) {
                return (object) [
                    'groupid' => $allgroupidmatch[$s->groupid],
                    'clsituationid' => $allclsituation[$s->clsituationid],
                    'starttime' => strftime('%d/%m/%Y', $s->starttime),
                    'endtime' => strftime('%d/%m/%Y', $s->endtime),
                ];
            }, $evalplan)));

    }

}
