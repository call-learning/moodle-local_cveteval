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

namespace local_cltools;
defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use core_user;
use local_cveteval\local\external\appr_crit;
use local_cveteval\local\external\appraisal;
use local_cveteval\local\external\auth;
use local_cveteval\local\external\cevalgrid;
use local_cveteval\local\external\clsituation;
use local_cveteval\local\external\criterion;
use local_cveteval\local\external\evalplan;
use local_cveteval\local\external\group_assign;
use local_cveteval\local\external\role;
use local_cveteval\local\external\user_profile;
use local_cveteval\local\external\user_type;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\role\entity as role_entity;

global $CFG;

require_once($CFG->libdir . '/externallib.php');

/**
 * API tests
 *
 * @package     local_cltools
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cveteval_api_testcase extends advanced_testcase {

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
     * Test if the User Type API is functional
     */
    public function test_get_user_type() {
        $this->assertEquals(
            (object)
            ['type' => role_entity::ROLE_SHORTNAMES[role_entity::ROLE_STUDENT_ID]],
            user_type::execute((core_user::get_user_by_username('etu1'))->id));
        $this->assertEquals(
            (object)
            ['type' => role_entity::ROLE_SHORTNAMES[role_entity::ROLE_ASSESSOR_ID]],
            user_type::execute((core_user::get_user_by_username('resp1'))->id));
        // Obs 1 to 5 are also assessors.
        $this->assertEquals(
            (object)
            ['type' => role_entity::ROLE_SHORTNAMES[role_entity::ROLE_ASSESSOR_ID]],
            user_type::execute((core_user::get_user_by_username('obs1'))->id));
        $this->assertEquals(
            (object)
            ['type' => role_entity::ROLE_SHORTNAMES[role_entity::ROLE_APPRAISER_ID]],
            user_type::execute((core_user::get_user_by_username('obs6'))->id));
        // This user was both in groups and in roles, default to student.
        $this->assertEquals(
            (object)
            ['type' => role_entity::ROLE_SHORTNAMES[role_entity::ROLE_STUDENT_ID]],
            user_type::execute((core_user::get_user_by_username('obs7'))->id));
    }

    /**
     * Test if the User Type API is functional
     */
    public function test_get_user_profile() {
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
    }

    /**
     * Test an API function
     */
    public function test_get_get_appraisal() {
        $user1 = core_user::get_user_by_username('etu1');
        $user2 = core_user::get_user_by_username('etu2');
        create_appraisal_for_students($user1->id, null, false);
        create_appraisal_for_students($user2->id, null, false);
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
        $this->assertCount(8, $appraisals); // 2 appraisal per eval plan.
        $user2 = core_user::get_user_by_username('obs2'); // Now as obs1.
        $this->setUser($user2);
        $appraisals = appraisal::get();
        $this->assertNotEmpty($appraisals);
        $this->assertCount(4, $appraisals); // 2 appraisal per students and.

    }

    /**
     * Test an API function
     */
    public function test_get_get_appraisal_additionalnotinplan() {
        $user1 = core_user::get_user_by_username('etu1');
        $user2 = core_user::get_user_by_username('etu2');
        create_appraisal_for_students($user1->id, null, false);
        create_appraisal_for_students($user2->id, null, false);
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
    public function test_get_get_appraisal_crit() {
        $user1 = core_user::get_user_by_username('etu1');
        $user2 = core_user::get_user_by_username('etu2');
        create_appraisal_for_students($user1->id, null, false);
        create_appraisal_for_students($user2->id, null, false);
        $appraisalscrit = appr_crit::get();
        $this->assertEmpty($appraisalscrit);
        // Now, I am user 1, I should only get appraisal involving me (either as a student or appraiser).
        $this->setUser($user1);
        $appraisalscrit = appr_crit::get();
        $this->assertNotEmpty($appraisalscrit);
        // 6 appraisals, 240 criteria
        $this->assertCount(6 * 40, $appraisalscrit); // 6 situations for this user in his planning.
        $user2 = core_user::get_user_by_username('obs1'); // Now as obs1.
        $this->setUser($user2);
        $appraisalscrit = appr_crit::get();
        $this->assertNotEmpty($appraisalscrit);
        $this->assertCount(8 * 40, $appraisalscrit); // 6 situations for this user in his planning.
    }

    /**
     * Test an API function
     */
    public function test_get_get_situation() {
        $situations = clsituation::get();
        $this->assertNotEmpty($situations);
        // We retrieve all situations here.
        $this->assertEquals(['TMG', 'TMI', 'TUS'],
            array_values(array_map(
                function($s) {
                    return $s->idnumber;
                }, $situations)));

    }

    /**
     * Test an API function
     */
    public function test_get_get_role() {
        global $DB;
        $roles = role::get();
        $allusersmatch =
            $DB->get_records_menu('user', null, '', 'id,username');
        $allclsituation =
            $DB->get_records_menu('local_cveteval_clsituation', null, '', 'id,title');
        $this->assertNotEmpty($roles);
        // We retrieve all situations here.
        $this->assertEquals(
            [
                (object) array(
                    'userid' => 'obs1',
                    'clsituationid' => 'Consultations de médecine générale',
                    'type' => role_entity::ROLE_APPRAISER_ID,
                ),
                (object) array(
                    'userid' => 'obs2',
                    'clsituationid' => 'Consultations de médecine générale',
                    'type' => role_entity::ROLE_APPRAISER_ID,
                ),
                (object) array(
                    'userid' => 'resp1',
                    'clsituationid' => 'Consultations de médecine générale',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),
                (object) array(
                    'userid' => 'obs1',
                    'clsituationid' => 'Consultations de médecine générale',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'obs2',
                    'clsituationid' => 'Consultations de médecine générale',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'resp2',
                    'clsituationid' => 'Médecine interne',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'obs3',
                    'clsituationid' => 'Médecine interne',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'obs4',
                    'clsituationid' => 'Médecine interne',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'obs1',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_APPRAISER_ID,
                ),

                (object) array(
                    'userid' => 'obs7',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_APPRAISER_ID,
                ),

                (object) array(
                    'userid' => 'obs6',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_APPRAISER_ID,
                ),

                (object) array(
                    'userid' => 'resp1',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'resp3',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),

                (object) array(
                    'userid' => 'obs5',
                    'clsituationid' => 'Urgences-Soins intensifs',
                    'type' => role_entity::ROLE_ASSESSOR_ID,
                ),
            ]
            ,
            array_values(array_map(function($r) use ($allusersmatch, $allclsituation) {
                return (object) [
                    'userid' => $allusersmatch[$r->userid],
                    'clsituationid' => $allclsituation[$r->clsituationid],
                    'type' => $r->type,
                ];
            }, $roles)));

    }

    /**
     * Test an API function
     */
    public function test_get_get_criterion() {
        $criteria = criterion::get();
        $this->assertNotEmpty($criteria);
        // We retrieve all situations here.
        $this->assertEquals(
            ['Q001', 'Q002', 'Q003', 'Q004', 'Q005', 'Q006', 'Q007', 'Q008', 'Q009', 'Q010', 'Q011', 'Q012', 'Q013', 'Q014', 'Q015',
                'Q016', 'Q017', 'Q018', 'Q019', 'Q020', 'Q021', 'Q022', 'Q023', 'Q024', 'Q025', 'Q026', 'Q027', 'Q028', 'Q029',
                'Q030', 'Q031', 'Q032', 'Q033', 'Q034', 'Q035', 'Q036', 'Q037', 'Q038', 'Q039', 'Q040'],
            array_values(array_map(function($s) {
                return $s->idnumber;
            }, $criteria)));

    }

    /**
     * Test an API function
     */
    public function test_get_get_group_assign() {
        global $DB;
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

    /**
     * Test an API function
     */
    public function test_get_get_evalgrid() {
        $evalgrid = cevalgrid::get();
        $this->assertNotEmpty($evalgrid);
        $this->assertCount(40, $evalgrid);
    }

    /**
     * Test an API function
     */
    public function test_get_get_evalplan_no_user() {
        // First, no user logged in.
        $evalplan = evalplan::get();
        $this->assertEmpty($evalplan);
    }

    /**
     * Test an API function
     */
    public function test_get_get_evalplan_student() {
        global $DB;
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
        $user1 = core_user::get_user_by_username('obs2'); // Obs2 is only in one situation.
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

    /**
     * All eval plans
     */
    public function test_get_idplist() {
        global $CFG;
        $this->resetAfterTest();
        $CFG->auth = $CFG->auth . ',cas';
        $this->assertEquals(auth::idp_list(), []);
    }

}
