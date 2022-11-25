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

namespace local_cveteval\local\datamigration;

use grade_scale;
use local_cveteval\local\datamigration\helpers\user_data_migration_helper;
use local_cveteval\local\datamigration\matchers\criterion;
use local_cveteval\local\datamigration\matchers\group;
use local_cveteval\local\datamigration\matchers\planning;
use local_cveteval\local\datamigration\matchers\role;
use local_cveteval\local\persistent\final_evaluation\entity as final_evaluation_entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\output\dmc_entity_renderer_base;
use local_cveteval\test\assessment_test_trait;
use stdClass;

/**
 * Data migration test
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_migration_test extends \advanced_testcase {

    use assessment_test_trait;

    /**
     * @var data_model_matcher $dm
     */
    private $dm;

    /**
     * @var object $oldentities
     */
    private $oldentities;
    /**
     * @var object $newentities
     */
    private $newentities;
    /**
     * @var object $planstart
     */
    private $planstart;
    /**
     * @var object $planend
     */
    private $planend;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->planstart = new \DateTimeImmutable("now", new \DateTimeZone("UTC"));
        $this->planend = $this->planstart->add(new \DateInterval("P1D"));;

        $historyold = new history_entity(0, (object) ['idnumber' => 'history1', 'comments' => '', 'isactive' => true]);
        $historyold->create();
        history_entity::set_current_id($historyold->get('id'));
        $this->oldentities = (object) [
            'criteria' => [],
            'situations' => [],
            'evalplans' => [],
            'students' => [],
            'assessors' => [],
            'appraisals' => [],
        ];
        [$this->oldentities->criteria, $this->oldentities->situations, $this->oldentities->evalplans,
            $this->oldentities->students, $this->oldentities->assessors, $this->oldentities->appraisals] =
            $this->set_up($this->get_sample_origin1($this->planstart, $this->planend));

        $historynew = new history_entity(0, (object) ['idnumber' => 'history2', 'comments' => '', 'isactive' => true]);
        $historynew->create();
        history_entity::set_current_id($historynew->get('id'));
        $this->newentities = (object) [
            'criteria' => [],
            'situations' => [],
            'evalplans' => [],
            'students' => [],
            'assessors' => [],
            'appraisals' => [],
        ];
        [$this->newentities->criteria, $this->newentities->situations, $this->newentities->evalplans,
            $this->newentities->students, $this->newentities->assessors, $this->newentities->appraisals] =
            $this->set_up($this->get_sample_dest2($this->planstart, $this->planend));

        history_entity::reset_current_id();
        $this->dm = new data_model_matcher($historyold->get('id'), $historynew->get('id'));

    }

    /**
     * History 1
     *
     * @param \DateTimeImmutable $planstart
     * @param \DateTimeImmutable $planend
     * @return \stdClass
     */
    protected function get_sample_origin1($planstart, $planend) {
        $sample = new \stdClass();

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
                'groupname' => 'Group 2bis',
                'clsituationidnumber' => 'SIT2',
                'starttime' => $planstart->getTimestamp(),
                'endtime' => $planend->getTimestamp(),
            ],
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
                'studentname' => 'student2',
                'appraisername' => 'assessor2',
                'evalplandatestart' => $planstart->format("d M Y"),
                'evalplandateend' => $planend->format("d M Y"),
                'evalplansituation' => 'SIT2',
                'evalplangroup' => 'Group 2',
                'context' => 'Context',
                'contextformat' => FORMAT_PLAIN,
                'comment' => 'Context',
                'commentformat' => FORMAT_PLAIN,
                'criteria' => $sample->criteriaeval
            ],
        ];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2']];
        return $sample;
    }

    /**
     * History 2
     *
     * @param \DateTimeImmutable $planstart
     * @param \DateTimeImmutable $planend
     * @return \stdClass
     */
    protected function get_sample_dest2($planstart, $planend) {
        $sample = new \stdClass();

        $sample->criteria = [
            [
                'evalgrididnumber' => 'evalgrid',
                'idnumber' => 'criterion1',
                'parentid' => 0,
                'sort' => 1
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'idnumber' => 'criterion2',
                'parentidnumber' => 'criterion1',
                'sort' => 1
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'idnumber' => 'criterion1bis',
                'parentidnumber' => 'criterion2',
                'sort' => 1
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
                'expectedevalsnb' => 2
            ],
            [
                'evalgrididnumber' => 'evalgrid',
                'title' => 'Situation 3',
                'description' => 'Situation desc',
                'descriptionformat' => FORMAT_PLAIN,
                'idnumber' => 'SIT3',
                'expectedevalsnb' => 1
            ],
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
                'groupname' => 'Group 3',
                'clsituationidnumber' => 'SIT2',
                'starttime' => $planstart->getTimestamp(),
                'endtime' => $planend->getTimestamp(),
            ],
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
                'appraisername' => 'assessor2',
                'evalplandatestart' => $planstart->format("d M Y"),
                'evalplandateend' => $planend->format("d M Y"),
                'evalplansituation' => 'SIT2',
                'evalplangroup' => 'Group 2',
                'context' => 'Context',
                'contextformat' => FORMAT_PLAIN,
                'comment' => 'Context',
                'commentformat' => FORMAT_PLAIN,
                'criteria' => $sample->criteriaeval
            ],
        ];
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor3' => 'SIT2']; // Assessor 2 is not present anymore.
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2'], 'student3' => ['Group 3']];
        return $sample;
    }

    /**
     * Test general migration of data
     *
     * Expected:
     * student 1 appraisal (SIT1, Group1) => student 1 appraisal (SIT1, Group1)
     * student 2 appraisal (SIT2, Group2) => student 2 appraisal (SIT2, Group1)
     * Assessor2 is changed to Assessor 3.
     */
    public function test_migration() {
        $data = new stdClass();
        $data->matchedentities = $this->dm->get_matched_entities_list();
        $data->unmatchedentities = $this->dm->get_unmatched_entities_list();
        $data->orphanedentities = $this->dm->get_orphaned_entities_list();

        $this->setup_migration_orphaned_entities($data); // Adjust the data for the migration.

        // Create Final evaluation.
        history_entity::set_current_id($this->dm->get_origin_id());
        $psituationfrom = \local_cveteval\local\persistent\situation\entity::get_record(['idnumber' => 'SIT2']);
        $pgroupfrom = \local_cveteval\local\persistent\group\entity::get_record(['name' => 'Group 2bis']);
        $planningfrom = \local_cveteval\local\persistent\planning\entity::get_record(['groupid' => $pgroupfrom->get('id'),
            'clsituationid' => $psituationfrom->get('id')]);
        $scaleid = get_config('local_cveteval', 'grade_scale');
        $scale = grade_scale::fetch(array('id' => $scaleid));
        $scaleitems = $scale->load_items();
        $evalinfo = new final_evaluation_entity(0,
            (object) [
                'studentid' => (array_values($this->oldentities->students)[0])->id,
                'assessorid' => (array_values($this->oldentities->assessors)[0])->id,
                'evalplanid' => $planningfrom->get('id'),
                'comment' => 'Test',
                'grade' => array_keys($scaleitems)[1]
            ]
        );
        $evalinfo->save();
        history_entity::disable_history();

        // Convert for checks.
        $convertedappraisalsinfo =
            user_data_migration_helper::convert_origin_appraisals(dmc_entity_renderer_base::ACTIONABLE_CONTEXTS, $data);
        $convertedfinalevalsinfo =
            user_data_migration_helper::convert_origin_finaleval(dmc_entity_renderer_base::ACTIONABLE_CONTEXTS, $data);

        // Checks.
        $this->assertNotEmpty($convertedappraisalsinfo);
        $this->assertNotEmpty($convertedfinalevalsinfo);
        $this->assertCount(2, $convertedappraisalsinfo);
        $this->assertCount(1, $convertedfinalevalsinfo);
    }

    /**
     * Setup data for migration
     *
     * @param object $data
     * @return void
     */
    protected function setup_migration_orphaned_entities(&$data) {
        // Make sure we match the orphaned entities first.
        history_entity::set_current_id($this->dm->get_origin_id());
        $criterionfrom = \local_cveteval\local\persistent\criterion\entity::get_record(['idnumber' => 'criterion1bis']);
        $groupfrom = \local_cveteval\local\persistent\group\entity::get_record(['name' => 'Group 2bis']);

        // Create a final evaluation.
        $psituationfrom = \local_cveteval\local\persistent\situation\entity::get_record(['idnumber' => 'SIT2']);
        $pgroupfrom = \local_cveteval\local\persistent\group\entity::get_record(['name' => 'Group 2bis']);
        $planningfrom = \local_cveteval\local\persistent\planning\entity::get_record(['groupid' => $pgroupfrom->get('id'),
            'clsituationid' => $psituationfrom->get('id')]);
        $assessor2id = 0;
        foreach ($this->oldentities->assessors as $assessor) {
            if ($assessor->username == 'assessor2') {
                $assessor2id = $assessor->id;
            }
        }
        $rolefrom = \local_cveteval\local\persistent\role\entity::get_record([
            'clsituationid' => $psituationfrom->get('id'),
            'userid' => $assessor2id,
            'type' => \local_cveteval\local\persistent\role\entity::ROLE_ASSESSOR_ID
        ]);
        history_entity::set_current_id($this->dm->get_dest_id());
        $criterionto = \local_cveteval\local\persistent\criterion\entity::get_record(['idnumber' => 'criterion1bis']);
        $groupto = \local_cveteval\local\persistent\group\entity::get_record(['name' => 'Group 3']);
        $psituationto = \local_cveteval\local\persistent\situation\entity::get_record(['idnumber' => 'SIT2']);
        $pgroupto = \local_cveteval\local\persistent\group\entity::get_record(['name' => 'Group 2']);
        $planningto = \local_cveteval\local\persistent\planning\entity::get_record([
            'groupid' => $pgroupto->get('id'),
            'clsituationid' => $psituationto->get('id')
        ]);
        $assessor3id = 0;
        foreach ($this->newentities->assessors as $assessor) {
            if ($assessor->username == 'assessor3') {
                $assessor3id = $assessor->id;
            }
        }
        $roleto = \local_cveteval\local\persistent\role\entity::get_record([
            'clsituationid' => $psituationto->get('id'),
            'userid' => $assessor3id,
            'type' =>
                \local_cveteval\local\persistent\role\entity::ROLE_ASSESSOR_ID
        ]);
        history_entity::disable_history();
        $data->orphanedentities[criterion::get_entity()][$criterionfrom->get('id')] = $criterionto->get('id');
        $data->orphanedentities[group::get_entity()][$groupfrom->get('id')] = $groupto->get('id');
        $data->orphanedentities[planning::get_entity()][$planningfrom->get('id')] = $planningto->get('id');
        $data->orphanedentities[role::get_entity()][$rolefrom->get('id')] = $roleto->get('id');
    }

    /**
     * Get appraisals for student
     *
     * @param int $studentindex
     * @param \DateTimeImmutable $planstart
     * @param \DateTimeImmutable $planend
     * @return object
     */
    private function get_appraisals_students($studentindex, $planstart, $planend) {
        $daystart = userdate($planstart->getTimestamp(), get_string('strftimedate', 'core_langconfig'));
        $dayend = userdate($planend->getTimestamp(), get_string('strftimedate', 'core_langconfig'));
        $planningrootlabel = "{$daystart}/{$dayend}";
        $appraisals = [
            1 => [
                'student' => 'student1 student1',
                'appraiser' => 'assessor1 assessor1',
                'planning' => "$planningrootlabel - Group 1 / SIT1",
                'criteria' =>
                    [
                        (object) [
                            'student' => 'student1 student1',
                            'appraiser' => 'assessor1 assessor1',
                            'planning' => "$planningrootlabel - Group 1 / SIT1",
                            'criterion' => '(criterion1)',
                            'grade' => '1',
                        ],
                        (object) [
                            'student' => 'student1 student1',
                            'appraiser' => 'assessor1 assessor1',
                            'planning' => "$planningrootlabel - Group 1 / SIT1",
                            'criterion' => '(criterion2)',
                            'grade' => '3',
                        ],
                    ],
            ],
            2 => [
                'student' => 'student2 student2',
                'appraiser' => 'assessor3 assessor3',
                'planning' => "$planningrootlabel - Group 2 / SIT2",
                'criteria' =>
                    [
                        (object) [
                            'student' => 'student2 student2',
                            'appraiser' => 'assessor3 assessor3',
                            'planning' => "$planningrootlabel - Group 2 / SIT2",
                            'criterion' => '(criterion1bis)',
                            'grade' => '2',
                        ],
                    ],
            ]
        ];
        return (object) $appraisals[$studentindex];
    }
}

