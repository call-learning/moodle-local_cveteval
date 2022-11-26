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
class api_user_test extends advanced_testcase {


    /**
     * setUp all and do not reset between the tests (as the model should not change)
     * This speeds up the test greatly.
     */
    public static function setUpBeforeClass(): void {
        test_utils::setup_from_shortsample();
    }

    /**
     * Reset data after all tests
     *
     * @return void
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
     * Test if the User Type API is functional
     */
    public function test_get_user_type() {
        $this->setAdminUser();
        $this->assertEquals(
            (object)
            ['type' => role_entity::get_type_shortname(role_entity::ROLE_STUDENT_ID)],
            user_type::execute((core_user::get_user_by_username('etu1'))->id));
        $this->assertEquals(
            (object)
            ['type' => role_entity::get_type_shortname(role_entity::ROLE_ASSESSOR_ID)],
            user_type::execute((core_user::get_user_by_username('resp1'))->id));
        // Obs 1 to 5 are also assessors.
        $this->assertEquals(
            (object)
            ['type' => role_entity::get_type_shortname(role_entity::ROLE_APPRAISER_ID)],
            user_type::execute((core_user::get_user_by_username('obs1'))->id));
        // This user was both in groups and in roles, default to student.
        $this->assertEquals(
            (object)
            ['type' => role_entity::get_type_shortname(role_entity::ROLE_STUDENT_ID)],
            user_type::execute((core_user::get_user_by_username('obs6'))->id));
        $this->assertEquals(
            (object)
            ['type' => role_entity::get_type_shortname(role_entity::ROLE_STUDENT_ID)],
            user_type::execute((core_user::get_user_by_username('obs7'))->id));
    }

    /**
     * Test if the User Type API is functional
     */
    public function test_get_user_profile() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $userid = (core_user::get_user_by_username('etu1'))->id;
        $this->assertEquals(
            [
                'userid' => $userid,
                'fullname' => 'Adéla Veselá',
                'firstname' => '',
                'lastname' => '',
                'username' => 'anonymous',
                'userpictureurl' => 'https://www.example.com/moodle/theme/image.php/_s/boost/core/1/u/f1'],
            (array) user_profile::execute(intval($userid)));
        $this->setAdminUser();
        $this->assertEquals(
            [
                'userid' => $userid,
                'fullname' => 'Adéla Veselá',
                'firstname' => 'Adéla',
                'lastname' => 'Veselá',
                'username' => 'etu1',
                'userpictureurl' => 'https://www.example.com/moodle/theme/image.php/_s/boost/core/1/u/f1'],
            (array) user_profile::execute(intval($userid)));
    }

    /**
     * Test an API function
     */
    public function test_get_get_role() {
        global $DB;
        $this->setAdminUser();
        $roles = role::get();
        $allusersmatch =
            $DB->get_records_menu('user', null, '', 'id,username');
        $allclsituation =
            $DB->get_records_menu('local_cveteval_clsituation', null, '', 'id,title');
        $this->assertNotEmpty($roles);
        // We retrieve all situations here.
        $results = [
            [
                'userid' => 'obs1',
                'clsituationid' => 'Consultations de médecine générale',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
            [
                'userid' => 'obs2',
                'clsituationid' => 'Consultations de médecine générale',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
            [
                'userid' => 'resp1',
                'clsituationid' => 'Consultations de médecine générale',
                'type' => role_entity::ROLE_ASSESSOR_ID,
            ],
            [
                'userid' => 'resp2',
                'clsituationid' => 'Médecine interne',
                'type' => role_entity::ROLE_ASSESSOR_ID,
            ],
            [
                'userid' => 'obs2',
                'clsituationid' => 'Médecine interne',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
            [
                'userid' => 'resp3',
                'clsituationid' => 'Urgences-Soins intensifs',
                'type' => role_entity::ROLE_ASSESSOR_ID,
            ],
            [
                'userid' => 'resp1',
                'clsituationid' => 'Urgences-Soins intensifs',
                'type' => role_entity::ROLE_ASSESSOR_ID,
            ],
            [
                'userid' => 'obs2',
                'clsituationid' => 'Urgences-Soins intensifs',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
            [
                'userid' => 'obs3',
                'clsituationid' => 'Urgences-Soins intensifs',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
            [
                'userid' => 'obs7',
                'clsituationid' => 'Urgences-Soins intensifs',
                'type' => role_entity::ROLE_APPRAISER_ID,
            ],
        ];
        $transformedarray = array_values(array_map(function($r) use ($allusersmatch, $allclsituation) {
            return (object) [
                'userid' => $allusersmatch[$r->userid],
                'clsituationid' => $allclsituation[$r->clsituationid],
                'type' => $r->type,
            ];
        }, $roles));
        foreach ($results as $r) {
            $this->assertTrue(in_array((object) $r, $transformedarray),
                "userid: {$r['userid']}, situation: {$r['clsituationid']}, type: {$r['type']}");
        }

    }


    /**
     * Test an API function
     */
    public function test_get_get_group_assign() {
        global $DB;
        $this->setAdminUser();
        $groupassign = group_assign::get();
        $this->assertNotEmpty($groupassign);
        $this->assertCount(5, $groupassign);
        $allstudentsid =
            $DB->get_fieldset_select('user', 'id', $DB->sql_like('username', ':namelike'),
                array('namelike' => '%etu%'));
        $allstudentsid[] = (core_user::get_user_by_username('obs7'))->id;
        // We retrieve all situations here.
        $this->assertEquals(
            $allstudentsid,
            array_values(array_map(function($s) {
                return $s->studentid;
            }, $groupassign)));

    }

}
