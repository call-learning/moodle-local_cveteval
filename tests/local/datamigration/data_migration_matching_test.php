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

use local_cveteval\local\datamigration\matchers\criterion as criterion_matcher;
use local_cveteval\local\datamigration\matchers\evaluation_grid as evaluation_grid_matcher;
use local_cveteval\local\datamigration\matchers\group as group_matcher;
use local_cveteval\local\datamigration\matchers\group_assignment as group_assignment_matcher;
use local_cveteval\local\datamigration\matchers\planning as planning_matcher;
use local_cveteval\local\datamigration\matchers\role as role_matcher;
use local_cveteval\local\datamigration\matchers\situation as situation_matcher;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\test\assessment_test_trait;

/**
 * Data migration test
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_migration_matching_test extends \advanced_testcase {

    use assessment_test_trait;

    /**
     * @var data_model_matcher
     */
    protected $dm;

    /**
     * @var object $oldentities
     */
    protected $oldentities;
    /**
     * @var object $newentities
     */
    protected $newentities;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $planstart = new \DateTimeImmutable("now", new \DateTimeZone("UTC"));
        $planend = $planstart->add(new \DateInterval("P1D"));

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
            $this->set_up($this->get_sample_origin1($planstart, $planend));

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
            $this->set_up($this->get_sample_dest2($planstart, $planend));

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
        $sample->assessors = ['assessor1' => 'SIT1', 'assessor2' => 'SIT2'];
        $sample->students = ['student1' => ['Group 1'], 'student2' => ['Group 1', 'Group 2'], 'student3' => ['Group 3']];
        return $sample;
    }

    /**
     * Test that situation 1 and 2 are matched but situation 3 is not. There is not orphaned entities (all situation
     * from the origin are also in the new dest).
     * Note that situation 2 has more evalnumber so we might need to alert manager / user on that.
     */
    public function test_match_situations() {
        $smatcher = new situation_matcher($this->dm);
        $matchedsituations = $smatcher->get_matched_origin_entities();
        $unmatchedsituations = $smatcher->get_unmatched_dest_entities();
        $orphanedentities = $smatcher->get_orphaned_origin_entities();
        $this->assert_entities_name_matches(['SIT1', 'SIT2'], $matchedsituations, situation_entity::class);
        $this->assert_entities_name_matches(['SIT3'], $unmatchedsituations, situation_entity::class);
        // Situation 3 is not in the old model.
        $this->assertEmpty($orphanedentities);
    }

    /**
     * Get matched name
     *
     * @param string $matchedname
     * @param array $entitiesidarray
     * @param string $entityclassname
     * @param string $fieldname
     * @return void
     */
    public function assert_entities_name_matches($matchedname, $entitiesidarray, $entityclassname, $fieldname = 'idnumber') {
        $this->assertEquals($matchedname,
            $this->get_field_from_entities_id($entitiesidarray, $entityclassname, $fieldname)
        );
    }

    /**
     * Get ID array
     *
     * @param array $entityidarray
     * @param string $entityclass
     * @param string $fieldname
     * @return array
     */
    protected function get_field_from_entities_id($entityidarray, $entityclass, $fieldname = 'idnumber') {
        return array_values(
            array_map(
                function($id) use ($entityclass, $fieldname) {
                    $e = new $entityclass($id);
                    return $e->get($fieldname);
                },
                $entityidarray
            )
        );
    }

    /**
     * criterion1bis is orphaned as it is not the same in the new dest (parent is different)
     */
    public function test_match_criterion() {
        $smatcher = new criterion_matcher($this->dm);
        $matchedcriterion = $smatcher->get_matched_origin_entities();
        $unmatchedcriterion = $smatcher->get_unmatched_dest_entities();
        $orphanedcriterion = $smatcher->get_orphaned_origin_entities();
        $generator = function() {
            for ($i = 1; $i < 41; $i++) {
                yield sprintf("Q%'.03d", $i);
            }
        };
        $fourtycriterion = iterator_to_array($generator()); // The common criteria are a match.
        $this->assert_entities_name_matches(array_merge($fourtycriterion, ['criterion1', 'criterion2']), $matchedcriterion,
            criterion_entity::class);
        $this->assert_entities_name_matches(['criterion1bis'], $unmatchedcriterion, criterion_entity::class);
        $this->assert_entities_name_matches(['criterion1bis'], array_flip($orphanedcriterion),
            criterion_entity::class); // Not the same parent.
    }

    /**
     * The new and old eval grid matched.
     */
    public function test_match_evaluation_grid() {
        $smatcher = new evaluation_grid_matcher($this->dm);
        $matchedegrid = $smatcher->get_matched_origin_entities();
        $unmatchedegrid = $smatcher->get_unmatched_dest_entities();
        $orphanedegrid = $smatcher->get_orphaned_origin_entities();
        $this->assert_entities_name_matches(['DEFAULTGRID', 'evalgrid'], $matchedegrid, evaluation_grid_entity::class);
        $this->assertEmpty($unmatchedegrid);
        $this->assertEmpty($orphanedegrid);
    }

    /**
     * Test that group is matched
     *
     * Except Group2bis which is not in the new list, and Group3 that appears in the new dest, Group 1 and 2 are matched.
     */
    public function test_match_group() {
        $smatcher = new group_matcher($this->dm);
        $matchedgroup = $smatcher->get_matched_origin_entities();
        $unmatchedgroup = $smatcher->get_unmatched_dest_entities();
        $orphanedgroup = $smatcher->get_orphaned_origin_entities();
        $this->assert_entities_name_matches(['Group 1', 'Group 2'], $matchedgroup, group_entity::class, "name");
        $this->assert_entities_name_matches(['Group 3'], $unmatchedgroup, group_entity::class, "name");
        $this->assert_entities_name_matches(['Group 2bis'], array_flip($orphanedgroup), group_entity::class, "name");
    }

    /**
     * Test that group assignment matched
     *
     * @return void
     */
    public function test_match_group_assignment() {
        $smatcher = new group_assignment_matcher($this->dm);
        $matchedgroupa = $smatcher->get_matched_origin_entities();
        $unmatchedgroupa = $smatcher->get_unmatched_dest_entities();
        $orphanedgroupa = $smatcher->get_orphaned_origin_entities();

        $this->assert_entities_name_matches(['Group 1', 'Group 1', 'Group 2'],
            $this->get_field_from_entities_id($matchedgroupa, group_assignment_entity::class, "groupid"),
            group_entity::class,
            "name"
        );
        $this->assert_entities_name_matches(['Group 3'],
            $this->get_field_from_entities_id($unmatchedgroupa, group_assignment_entity::class, "groupid"),
            group_entity::class,
            "name"
        );
        $this->assertEmpty($orphanedgroupa);
    }

    /**
     * Test that planning matched
     *
     * @return void
     */
    public function test_match_planning() {
        $smatcher = new planning_matcher($this->dm);
        $matchedplanning = $smatcher->get_matched_origin_entities();
        $unmatchedplanning = $smatcher->get_unmatched_dest_entities();
        $orphanedplanning = $smatcher->get_orphaned_origin_entities();

        $this->assert_entities_name_matches(['SIT1', 'SIT2'],
            $this->get_field_from_entities_id($matchedplanning, planning_entity::class, "clsituationid"),
            situation_entity::class
        );
        $this->assert_entities_name_matches(['SIT2'],
            $this->get_field_from_entities_id($unmatchedplanning, planning_entity::class, "clsituationid"),
            situation_entity::class
        );
        $this->assert_entities_name_matches(['SIT2'],
            $this->get_field_from_entities_id(array_flip($orphanedplanning), planning_entity::class, "clsituationid"),
            situation_entity::class
        );
    }

    /**
     * Test that role matched
     *
     * @return void
     */
    public function test_match_role() {
        $smatcher = new role_matcher($this->dm);
        $matchedroles = $smatcher->get_matched_origin_entities();
        $unmatchedroles = $smatcher->get_unmatched_dest_entities();
        $orphanedroles = $smatcher->get_orphaned_origin_entities();
        $this->assert_entities_name_matches(['SIT1', 'SIT1', 'SIT1', 'SIT2', 'SIT2', 'SIT2'],
            $this->get_field_from_entities_id($matchedroles, role_entity::class, "clsituationid"),
            situation_entity::class
        );
        $this->assert_entities_name_matches(['SIT1', 'SIT2'],
            $this->get_field_from_entities_id($unmatchedroles, role_entity::class, "clsituationid"),
            situation_entity::class
        );
        $this->assertEmpty($orphanedroles);
    }

    /**
     * Get ID array
     *
     * @param array $entityarray
     * @param string $fieldname
     * @return array
     */
    protected function get_field_from_entities($entityarray, $fieldname = 'idnumber') {
        return array_values(
            array_map(
                function($e) use ($fieldname) {
                    return $e->get($fieldname);
                }, $entityarray)
        );
    }
}
