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

namespace local_cveteval\test;

use local_cveteval;
use local_cveteval\local\persistent\history\entity as history_entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Test Utils
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait assessment_test_trait {
    /**
     * @var array $criteria all criteria
     */
    protected array $criteria = [];
    /**
     * @var array $situations all situations
     */
    protected array $situations = [];
    /**
     * @var array $evalplans all evalplans
     */
    protected array $evalplans = [];
    /**
     * @var array $students all students
     */
    protected array $students = [];
    /**
     * @var array $assessors all assessors
     */
    protected array $assessors = [];
    /**
     * @var array $appraisals
     */
    protected array $appraisals = [];

    /**
     * Create a set of data
     * @param $sample
     * @return array an array composed of [criteria, situations, evalplans, students, assessors, appraisals]
     * which themselves are array indexed by their respective id.
     */
    protected function set_up($sample) {
        [$criteria, $situations, $evalplans] = $this->create_simple_model(
            $sample->criteria,
            $sample->situations,
            $sample->evalplans
        );
        [$students, $assessors] =
            $this->create_simple_roles(
                $sample->assessors,
                $sample->students
            );

        $appraisals = $this->create_simple_appraisals($sample->appraisals);
        return [$criteria, $situations, $evalplans, $students, $assessors, $appraisals];
    }

    protected function get_sample_with_assessments() {
        $sample = new \stdClass();
        $planstart = time();
        $planend = time() + 3600 * 24;

        $sample->criteria = [
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
        $sample->criteriaeval = [
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
        $sample->situations = [
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 1',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT1',
                'expectedevalsnb' => 2
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 2',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT2',
                'expectedevalsnb' => 1
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 3',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT3',
                'expectedevalsnb' => 1
            ]
        ];
        $sample->evalplans = [
            [
                'groupname' => 'Group 1',
                'clsituationidnumber' => 'SIT1',
                'starttime' => $planstart,
                'endtime' => $planend
            ],
            [
                'groupname' => 'Group 2',
                'clsituationidnumber' => 'SIT2',
                'starttime' => $planstart,
                'endtime' => $planend
            ],
            [
                'groupname' => 'Group 2',
                'clsituationidnumber' => 'SIT3',
                'starttime' => $planstart,
                'endtime' => $planend
            ]

        ];
        $sample->appraisals = [
            [
                'studentname' => 'student1',
                'appraisername' => 'assessor1',
                'evalplandatestart' => $planstart,
                'evalplansituation' => 'SIT1',
                'context' => 'Context',
                'contextformat' => FORMAT_PLAIN,
                'comment' => 'Context',
                'commentformat' => FORMAT_PLAIN,
                'criteria' => $sample->criteriaeval
            ],
            [
                'studentname' => 'student1',
                'appraisername' => 'assessor1',
                'evalplandatestart' => $planstart,
                'evalplansituation' => 'SIT1',
                'context' => 'Context',
                'contextformat' => FORMAT_PLAIN,
                'comment' => 'Context',
                'commentformat' => FORMAT_PLAIN,
                'criteria' => $sample->criteriaeval
            ],
            [
                'studentname' => 'student1',
                'appraisername' => 'assessor2',
                'evalplandatestart' => $planstart,
                'evalplansituation' => 'SIT1',
                'context' => 'Context',
                'contextformat' => FORMAT_PLAIN,
                'comment' => 'Context',
                'commentformat' => FORMAT_PLAIN,
                'criteria' => $sample->criteriaeval
            ]
        ];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2']];
        return $sample;
    }

    protected function get_simple_model_1() {
        $sample = new \stdClass();
        $planstart = time();
        $planend = time() + 3600 * 24;

        $sample->criteria = [
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
            ]
        ];

        $sample->criteriaeval = [
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
        $sample->situations = [
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 1',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT1',
                'expectedevalsnb' => 2
            ]
        ];

        $sample->evalplans = [
            [
                'groupname' => 'Group 1',
                'clsituationidnumber' => 'SIT1',
                'starttime' => $planstart,
                'endtime' => $planend
            ]
        ];
        $sample->appraisals = [];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2']];
        return $sample;
    }

    protected function get_simple_model_2() {
        $sample = new \stdClass();
        $planstart = time();
        $planend = time() + 3600 * 24;

        $sample->criteria = [
            [
                'evalgrididnumber' => 'evalgrid',
                'idnumber' => 'criterion2',
                'parentid' => 0,
                'sort' => 1
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'idnumber' => 'criterion2bis',
                'parentidnumber' => 'criterion2',
                'sort' => 1
            ]
        ];
        $sample->criteriaeval = [
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
        $sample->situations = [
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 2',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT2',
                'expectedevalsnb' => 2
            ]
        ];
        $sample->evalplans = [];
        $sample->appraisals = [];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2']];
        return $sample;
    }

    public function create_simple_model($criteria, $situations, $evalplans) {
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $ccriteria = [];
        $csituations = [];
        $cevalplans = [];
        foreach ($criteria as $cdata) {
            if (!local_cveteval\local\persistent\evaluation_grid\entity::record_exists_select(
                "idnumber = :idnumber",
                [
                    'idnumber' => $cdata['evalgrididnumber']
                ])) {
                $cevetevalgenerator->create_evaluation_grid([
                    'name' => $cdata['evalgrididnumber'],
                    'idnumber' => $cdata['evalgrididnumber']
                ]);
            }
            $nentity = $cevetevalgenerator->create_criterion($cdata);
            $ccriteria[$nentity->get('id')] = $nentity;

        }
        // Create situation.
        foreach ($situations as $situation) {
            $nentity = $cevetevalgenerator->create_situation(
                $situation
            );
            $csituations[$nentity->get('id')] = $nentity;
        }
        foreach ($evalplans as $evalplan) {
            if (!local_cveteval\local\persistent\group\entity::record_exists_select("name = :name",
                [
                    'name' => $evalplan['groupname']
                ])) {
                $cevetevalgenerator->create_group([
                    'name' => $evalplan['groupname']
                ]);
            }
            $nentity = $cevetevalgenerator->create_evalplan(
                $evalplan
            );
            $cevalplans[$nentity->get('id')] = $nentity;
        }
        return [$ccriteria, $csituations, $cevalplans];
    }

    public function create_simple_roles($assessors, $students) {
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $genericgenerator = $this->getDataGenerator();
        $cstudents = [];
        $cassessors = [];
        // Create  students.
        foreach ($students as $studentname => $groupsname) {
            $currenstudent = \core_user::get_user_by_username($studentname);
            if (!$currenstudent) {
                $currenstudent = $genericgenerator->create_user(['username' => $studentname]);
            }
            $cstudents[$currenstudent->id] = $currenstudent;
            foreach ($groupsname as $groupname) {
                if (!local_cveteval\local\persistent\group\entity::record_exists_select("name = :name",
                    [
                        'name' => $groupname
                    ])) {
                    $cevetevalgenerator->create_group([
                        'name' => $groupname
                    ]);
                }
                $cevetevalgenerator->create_group_assign([
                    'studentname' => $studentname,
                    'groupname' => $groupname
                ]);
            }
        }
        // Create assessors and roles for students.
        foreach ($assessors as $assessorname => $situationname) {
            $currentassessor = \core_user::get_user_by_username($assessorname);
            if (!$currentassessor) {
                $currentassessor = $genericgenerator->create_user(['username' => $assessorname]);
            }
            $cassessors[$currentassessor->id] = $currentassessor;
            $cevetevalgenerator->create_role([
                'clsituationidnumber' => $situationname,
                'username' => $assessorname,
                'type' => \local_cveteval\local\persistent\role\entity::ROLE_ASSESSOR_ID
            ]);
            foreach ($cstudents as $student) {
                $cevetevalgenerator->create_role([
                    'clsituationidnumber' => $situationname,
                    'username' => $student->username,
                    'type' => \local_cveteval\local\persistent\role\entity::ROLE_STUDENT_ID
                ]);
            }
        }

        return [$cstudents, $cassessors];
    }

    public function create_simple_appraisals($appraisalsdefs) {
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $cappraisals = [];
        foreach ($appraisalsdefs as $appraisal) {
            $centity = $cevetevalgenerator->create_appraisal($appraisal);
            $cappraisals[$centity->get('id')] = $centity;
        }
        return $cappraisals;
    }

    protected function create_history($comments = "") {
        if (empty($idnumber)) {
            $dateandrandom = userdate(time(), get_string('strftimedatetimeshort')) . '-' . random_string(5);
            $idnumber = get_string('defaulthistoryidnumber', 'local_cveteval', $dateandrandom);
        }
        $history = new history_entity(0,
            (object) ['idnumber' => $idnumber, 'comments' => $comments, 'isactive' => true]);
        $history->create();
        return $history;
    }
}