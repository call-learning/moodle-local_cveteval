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
 * Assessment utils test
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\assessment;
defined('MOODLE_INTERNAL') || die();

class assessment_utils_testcase extends \advanced_testcase {
    const CRITERIA = [
        [
            'evalgrididnumber' => 'evalgrid',
            'idnumber' => 'criterion1',
            'parentid' => 0,
            'sort' => 1
        ],
        [
            'evalgrididnumber' => 'evalgrid',
            'idnumber' => 'criterion1bis',
            'parentidnumber' => 'criterion1',
            'sort' => 1
        ],
        [
            'evalgrididnumber' => 'evalgrid',
            'idnumber' => 'criterion2',
            'parentidnumber' => 'criterion1',
            'sort' => 1
        ]
    ];

    const SAMPLE_CRITERIA_EVAL =  [
        [
            'criterionidnumber' => 'criterion1',
            'grade' => 1,
            'comment' => 'Context crit1',
            'commentformat' => FORMAT_PLAIN,
        ],
        [
            'criterionidnumber' => 'criterion1bis',
            'grade' => 2,
            'comment' => 'Context crit1bis',
            'commentformat' => FORMAT_PLAIN,
        ],
        [
            'criterionidnumber' => 'criterion2',
            'grade' => 3,
            'comment' => 'Context crit2',
            'commentformat' => FORMAT_PLAIN,
        ]
    ];

    public function test_get_thissituation_list() {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $generator->create_evaluation_grid([
            'name' => 'EVALGRID0',
            'idnumber' => 'evalgrid'
        ]);
        foreach (self::CRITERIA as $cdata) {
            $generator->create_criterion($cdata);
        }
        // Create situation.
        $generator->create_situation(
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 1',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT1',
                'expectedevalsnb' => 2
            ]
        );
        // Eval plan.
        $generator->create_group([
            'name' => 'Group 1'
        ]);
        $evalplanstart = time() - 3600 * 24;
        $evalplan = $generator->create_evalplan([
            'groupname' => 'Group 1',
            'clsituationidnumber' => 'SIT1',
            'starttime' => $evalplanstart,
            'endtime' => time() + 3600 * 24
        ]);
        // Create 2 assessors.
        foreach (['assessor1', 'assessor2'] as $assessorname) {
            $assessors[] = $this->getDataGenerator()->create_user(['username' => $assessorname]);
            $generator->create_role([
                'clsituationidnumber' => 'SIT1',
                'username' => $assessorname,
                'type' => \local_cveteval\local\persistent\role\entity::ROLE_ASSESSOR_ID
            ]);
        }
        // Create  students.
        $student = $this->getDataGenerator()->create_user(['username' => 'student1']);
        $generator->create_role([
            'clsituationidnumber' => 'SIT1',
            'username' => 'student1',
            'type' => \local_cveteval\local\persistent\role\entity::ROLE_STUDENT_ID
        ]);
        $generator->create_group_assign([
            'studentname' => 'student1',
            'groupname' => 'Group 1'
        ]);
        // Create 2 appraisal for assessor 1
        $generator->create_appraisal([
            'studentname' => 'student1',
            'appraisername' => 'assessor1',
            'evalplandatestart' => $evalplanstart,
            'evalplansituation' => 'SIT1',
            'context' => 'Context',
            'contextformat' => FORMAT_PLAIN,
            'comment' => 'Context',
            'commentformat' => FORMAT_PLAIN,
            'criteria' => self::SAMPLE_CRITERIA_EVAL
        ]);
        $generator->create_appraisal([
            'studentname' => 'student1',
            'appraisername' => 'assessor1',
            'evalplandatestart' => $evalplanstart,
            'evalplansituation' => 'SIT1',
            'context' => 'Context',
            'contextformat' => FORMAT_PLAIN,
            'comment' => 'Context',
            'commentformat' => FORMAT_PLAIN,
            'criteria' => self::SAMPLE_CRITERIA_EVAL
        ]);
        $generator->create_appraisal([
            'studentname' => 'student1',
            'appraisername' => 'assessor2',
            'evalplandatestart' => $evalplanstart,
            'evalplansituation' => 'SIT1',
            'context' => 'Context',
            'contextformat' => FORMAT_PLAIN,
            'comment' => 'Context',
            'commentformat' => FORMAT_PLAIN,
            'criteria' => self::SAMPLE_CRITERIA_EVAL
        ]);
        $assessment = assessment_utils::get_thissituation_list($student->id, $evalplan->get('id'));
        $assessment->define_baseurl(new \moodle_url(''));
        $data = $assessment->retrieve_raw_data(10);
        // Check that we have one row.
        $this->assertCount(1, $data);
        $columnswithappraisergrade =  array_filter(
            (array)$data[0],
            function($keyname) {return strstr($keyname, 'appraisergrade' );},
            ARRAY_FILTER_USE_KEY);
        // Three assessments.
        $this->assertCount(3,$columnswithappraisergrade);
    }
}
