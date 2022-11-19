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

namespace local_cveteval\local\persistent;

use local_cveteval\local\assessment\assessment_utils;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\test\assessment_test_trait;

/**
 * Assessment utils test
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_with_history_advanced_test extends \advanced_testcase {

    use assessment_test_trait;

    /**
     * @var array|history_entity
     */
    protected $histories = [];

    /**
     * Setup test
     * @return void
     */
    public function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        $this->histories[] = $this->create_history();
        history_entity::set_current_id($this->histories[0]->get('id'));
        [$this->criteria, $this->situations, $this->evalplans, $this->students, $this->assessors, $this->appraisals] =
            $this->set_up($this->get_sample_with_assessments());
        $this->students = array_values($this->students);
        $this->evalplans = array_values($this->evalplans);
        $this->situations = array_values($this->situations);
        $this->appraisals = array_values($this->appraisals);
        $this->assessors = array_values($this->assessors);
    }

    /**
     * Test situation list
     *
     * @return void
     */
    public function test_get_thissituation_list() {
        $filterfunction = function($keyname) {
            return strstr($keyname, 'appraisergrade');
        };
        $assessment = assessment_utils::get_thissituation_list($this->students[0]->id, $this->evalplans[0]->get('id'));
        $data = $assessment->get_rows(10);
        // Check that we have one row.
        $this->assertCount(1, $data);
        $this->assertCount(3, array_filter((array) $data[0], $filterfunction, ARRAY_FILTER_USE_KEY));
        // Disable current history.
        $this->histories[0]->set('isactive', false);
        $this->histories[0]->update();

        // Import another set.
        $this->histories[] = $this->create_history();
        history_entity::set_current_id($this->histories[1]->get('id'));
        $sample = $this->get_sample_with_assessments();
        $sample->appraisals = array_slice($sample->appraisals, 0, 2);
        [$newcriteria, $newsituations, $newevalplans, $newstudents] = $this->set_up($sample);
        $newstudents = array_values($newstudents);
        $newevalplans = array_values($newevalplans);
        $assessment = assessment_utils::get_thissituation_list($newstudents[0]->id, $newevalplans[0]->get('id'));
        $data = $assessment->get_rows(10);
        $this->assertCount(1, $data);
        // Two assessments.
        $this->assertCount(2, array_filter((array) $data[0], $filterfunction, ARRAY_FILTER_USE_KEY));
        // Check that we have one row.
        $this->assertCount(1, $data);
    }

    /**
     * Get my student list
     *
     * @return void
     */
    public function test_get_mystudents_list() {
        $this->resetAfterTest();
        $this->setUser($this->assessors[0]);
        $studentlist = assessment_utils::get_mystudents_list($this->situations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have two row.
        $this->assertCount(2, $data);
        // Check student info.
        $this->assertEquals($this->students[0]->id, $data[0]->studentid);
        $this->assertEquals(3, $data[0]->appraisalcount);

        // Import another set.
        $newhistory = $this->create_history();
        $this->histories[] = $newhistory;
        history_entity::set_current_id($newhistory->get('id'));
        $sample = $this->get_sample_with_assessments();
        $sample->situations[0]['expectedevalsnb'] = 5;
        $sample->appraisals = array_slice($sample->appraisals, 0, 2);
        $sample->appraisals = array_slice($sample->appraisals, 0, 2);
        $sample->students = ['student2' => ['Group 1', 'Group 2']];
        [$newcriteria, $newsituations, $newevalplans, $newstudents] = $this->set_up($sample);
        $newsituations = array_values($newsituations);
        $newstudents = array_values($newstudents);

        $studentlist = assessment_utils::get_mystudents_list($newsituations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have two row.
        $this->assertCount(1, $data);
        // Check student info.
        $this->assertEquals($newstudents[0]->id, $data[0]->studentid);
        $this->assertEquals(5, $data[0]->appraisalrequired);

        // Now enable both histories.
        $this->histories[0]->set('isactive', true);
        $this->histories[0]->update();
        $this->histories[1]->set('isactive', true);
        $this->histories[1]->update();
        history_entity::reset_current_id();

        $studentlist = assessment_utils::get_mystudents_list($this->situations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have two row.
        $this->assertCount(2, $data); // Still two as we just look at one situation.
        $studentlist = assessment_utils::get_mystudents_list($newsituations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have two row.
        $this->assertCount(1, $data); // Still two as we just look at one situation.
    }

    /**
     * Get assessment criteria list
     *
     * @return void
     */
    public function test_get_assessmentcriteria_list() {
        $this->resetAfterTest();
        $this->setUser($this->assessors[0]);
        $criterialist = assessment_utils::get_assessmentcriteria_list($this->appraisals[0]->get('id'));
        $data = $criterialist->get_rows(10);
        // Check that we have two rows.
        $this->assertCount(1, $data);
        $this->assertCount(2, $data[0]->_children);
    }

    /**
     * Get situation for student  list
     *
     * @return void
     */
    public function test_get_situations_for_student() {
        $this->resetAfterTest();
        $this->setUser($this->assessors[0]);
        $criterialist = assessment_utils::get_situations_for_student($this->students[0]->id);
        $data = $criterialist->get_rows(10);
        // Check that we have two rows.
        $this->assertCount(1, $data);
        $criterialist = assessment_utils::get_situations_for_student($this->students[1]->id);
        $data = $criterialist->get_rows(10);
        // Check that we have two rows.
        $this->assertCount(3, $data);
    }
}
