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

use core_user;
use local_cveteval;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\role\entity;
use stdClass;

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
    protected $criteria = [];
    /**
     * @var array $situations all situations
     */
    protected $situations = [];
    /**
     * @var array $evalplans all evalplans
     */
    protected $evalplans = [];
    /**
     * @var array $students all students
     */
    protected $students = [];
    /**
     * @var array $assessors all assessors
     */
    protected $assessors = [];
    /**
     * @var array $appraisals
     */
    protected $appraisals = [];

    /**
     * Create a set of data
     *
     * @param object $sample
     * @return array an array composed of [criteria, situations, evalplans, students, assessors, appraisals]
     * which themselves are array indexed by their respective id.
     * @package   local_cveteval
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

    /**
     * Create a simple model for testing
     *
     * @param array $criteria
     * @param array $situations
     * @param array $evalplans
     * @return array[]
     * @package   local_cveteval
     */
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
            if (isset($cdata['evalgrididnumber'])) {
                $cdata['evalgridid'] = is_int($cdata['evalgrididnumber']) ? intval($cdata['evalgrididnumber']) :
                    local_cveteval\local\persistent\evaluation_grid\entity::get_record(['idnumber' => $cdata['evalgrididnumber']])
                        ->get('id');
                unset($cdata['evalgrididnumber']);
            }
            if (isset($cdata['parentidnumber'])) {
                $cdata['parentid'] = local_cveteval\local\persistent\criterion\entity::get_record(
                    array('idnumber' => $cdata['parentidnumber']))->get('id');
                unset($cdata['parentidnumber']);
            }
            $nentity = $cevetevalgenerator->create_criterion($cdata);
            $ccriteria[$nentity->get('id')] = $nentity;

        }
        // Create situation.
        foreach ($situations as $situation) {
            if ($situation['evalgrididnumber']) {
                $situation['evalgridid'] = local_cveteval\local\persistent\evaluation_grid\entity::get_record(
                    array('idnumber' => $situation['evalgrididnumber']))->get('id');
                unset($situation['evalgrididnumber']);
            }
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
            if (isset($evalplan['clsituationidnumber'])) {
                $evalplan['clsituationid'] = local_cveteval\local\persistent\situation\entity::get_record(
                    array('idnumber' => $evalplan['clsituationidnumber']))->get('id');
                unset($evalplan['clsituationidnumber']);
            }
            if (isset($evalplan['groupname'])) {
                $evalplan['groupid'] = local_cveteval\local\persistent\group\entity::get_record(
                    array('name' => $evalplan['groupname']))->get('id');
                unset($evalplan['groupname']);
            }
            $nentity = $cevetevalgenerator->create_planning(
                    $evalplan
            );
            $cevalplans[$nentity->get('id')] = $nentity;
        }
        return [$ccriteria, $csituations, $cevalplans];
    }

    /**
     * Create roles for testing
     *
     * @param array $assessors
     * @param array $students
     * @return array[]
     * @package   local_cveteval
     */
    public function create_simple_roles($assessors, $students) {
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $genericgenerator = $this->getDataGenerator();
        $cstudents = [];
        $cassessors = [];
        // Create  students.
        foreach ($students as $studentname => $groupsname) {
            $currenstudent = core_user::get_user_by_username($studentname);
            if (!$currenstudent) {
                $currenstudent = $genericgenerator->create_user(['username' => $studentname,
                        'firstname' => $studentname,
                        'lastname' => $studentname
                ]);
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
                $studentid = test_utils::get_from_username($studentname);
                $groupid = local_cveteval\local\persistent\group\entity::get_record(['name' => $groupname])->get('id');
                $cevetevalgenerator->create_group_assignment([
                    'studentid' => $studentid,
                    'groupid' => $groupid
                ]);
            }
        }
        // Create assessors and roles for students.
        foreach ($assessors as $assessorname => $situationname) {
            $currentassessor = core_user::get_user_by_username($assessorname);
            if (!$currentassessor) {
                $currentassessor = $genericgenerator->create_user(['username' => $assessorname,
                    'firstname' => $assessorname,
                    'lastname' => $assessorname
                ]);
            }
            $situationid = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $situationname))->get('id');
            $cassessors[$currentassessor->id] = $currentassessor;
            $cevetevalgenerator->create_role([
                'clsituationid' => $situationid,
                'userid' => test_utils::get_from_username($assessorname),
                'type' => entity::ROLE_ASSESSOR_ID
            ]);
            foreach ($cstudents as $student) {
                $cevetevalgenerator->create_role([
                    'clsituationid' => $situationid,
                    'userid' => test_utils::get_from_username($student->username),
                    'type' => entity::ROLE_STUDENT_ID
                ]);
            }
        }

        return [$cstudents, $cassessors];
    }

    /**
     * Create appraisals for testing
     *
     * @param array $appraisalsdefs
     * @return array[]
     * @package   local_cveteval
     */
    public function create_simple_appraisals($appraisalsdefs) {
        $cevetevalgenerator = $this->getDataGenerator()->get_plugin_generator('local_cveteval');
        $cappraisals = [];
        foreach ($appraisalsdefs as $appraisal) {
            $appraisal['studentid'] = test_utils::get_from_username($appraisal['studentname']);
            $appraisal['appraiserid'] = test_utils::get_from_username($appraisal['appraisername']);
            unset($appraisal['studentname']);
            unset($appraisal['appraisername']);
            $subcriteria = null;
            if (!empty($appraisal['criteria'])) {
                $subcriteria = $appraisal['criteria'];
                unset($appraisal['criteria']);
            }
            $evaliplanid = test_utils::get_evalplanid_from_date_and_situation(
                $appraisal['evalplandatestart'],
                $appraisal['evalplandateend'],
                $appraisal['evalplansituation'],
                $appraisal['evalplangroup']
            );
            unset($appraisal['evalplandatestart']);
            unset($appraisal['evalplandateend']);
            unset($appraisal['evalplansituation']);
            unset($appraisal['evalplangroup']);
            if ($evaliplanid) {
                $appraisal['evalplanid'] = $evaliplanid;
            }
            $appraisal = $cevetevalgenerator->create_appraisal($appraisal);
            $appraisalid = $appraisal->get('id');
            if (!empty($subcriteria)) {
                foreach ($subcriteria as $criterion) {
                    $criterion['appraisalid'] = $appraisalid;
                    $criterion['criterionid'] = local_cveteval\local\persistent\criterion\entity::get_record(
                        ['idnumber' => $criterion['criterionidnumber']]
                    )->get('id');
                    unset($criterion['criterionidnumber']);
                    $cevetevalgenerator->create_appraisal_criterion($criterion);
                }
            }
            $cappraisals[$appraisalid] = $appraisal;
        }
        return $cappraisals;
    }

    /**
     * Get sample with assessments
     *
     * @return stdClass
     * @package   local_cveteval
     */
    protected function get_sample_with_assessments() {
        $sample = new stdClass();
        $utc = new \DateTimeZone("UTC");
        $planstart = new \DateTimeImmutable("now", $utc);
        $planend = $planstart->add(new \DateInterval("P1D"));

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
                        'starttime' => $planstart->getTimestamp(),
                        'endtime' => $planend->getTimestamp(),
                ],
                [
                        'groupname' => 'Group 2',
                        'clsituationidnumber' => 'SIT2',
                        'starttime' => $planstart->getTimestamp(),
                        'endtime' => $planend->getTimestamp(),
                ],
                [
                        'groupname' => 'Group 2',
                        'clsituationidnumber' => 'SIT3',
                        'starttime' => $planstart->getTimestamp(),
                        'endtime' => $planend->getTimestamp(),
                ]

        ];
        $sample->appraisals = [
                [
                        'studentname' => 'student1',
                        'appraisername' => 'assessor1',
                        'evalplandatestart' => $planstart->format("d M Y"),
                        'evalplandateend' => $planend->format("d M Y"),
                        'evalplansituation' => 'SIT1',
                        'evalplangroup' => 'Group 1',
                        'context' => 'Context',
                        'contextformat' => FORMAT_PLAIN,
                        'comment' => 'Context',
                        'commentformat' => FORMAT_PLAIN,
                        'criteria' => $sample->criteriaeval
                ],
                [
                        'studentname' => 'student1',
                        'appraisername' => 'assessor1',
                        'evalplandatestart' => $planstart->format("d M Y"),
                        'evalplandateend' => $planend->format("d M Y"),
                        'evalplansituation' => 'SIT1',
                        'evalplangroup' => 'Group 1',
                        'context' => 'Context',
                        'contextformat' => FORMAT_PLAIN,
                        'comment' => 'Context',
                        'commentformat' => FORMAT_PLAIN,
                        'criteria' => $sample->criteriaeval
                ],
                [
                        'studentname' => 'student1',
                        'appraisername' => 'assessor2',
                        'evalplandatestart' => $planstart->format("d M Y"),
                        'evalplandateend' => $planend->format("d M Y"),
                        'evalplansituation' => 'SIT1',
                        'evalplangroup' => 'Group 1',
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

    /**
     * Get model 1
     *
     * @return stdClass
     * @package   local_cveteval
     */
    protected function get_simple_model_1() {
        $sample = new stdClass();
        $utc = new \DateTimeZone("UTC");
        $planstart = new \DateTimeImmutable("now", $utc);
        $planend = $planstart->add(new \DateInterval("P1D"));

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
                        'starttime' => $planstart->getTimestamp(),
                        'endtime' => $planend->getTimestamp()
                ]
        ];
        $sample->appraisals = [];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2']];
        return $sample;
    }

    /**
     * Get model 2
     *
     * @return stdClass
     * @package   local_cveteval
     */
    protected function get_simple_model_2() {
        $sample = new stdClass();

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

    /**
     * Create a new history entity
     *
     * @param string $comments
     * @return history_entity
     * @package   local_cveteval
     */
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
