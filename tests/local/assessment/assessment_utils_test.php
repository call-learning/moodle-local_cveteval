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

namespace local_cveteval\local\assessment;

use local_cveteval\test\assessment_test_trait;

/**
 * Assessment utils test
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessment_utils_test extends \advanced_testcase {
    use assessment_test_trait;

    /**
     * Setup before tests.
     */
    public function setUp() {
        parent::setUp();
        \local_cveteval\local\persistent\history\entity::disable_history_globally();
        $this->resetAfterTest();
        [$this->criteria, $this->situations, $this->evalplans, $this->students, $this->assessors, $this->appraisals] =
            $this->set_up($this->get_sample_with_assessments());
        $this->students = array_values($this->students);
        $this->evalplans = array_values($this->evalplans);
        $this->situations = array_values($this->situations);
        $this->appraisals = array_values($this->appraisals);
        $this->assessors = array_values($this->assessors);
    }

    /**
     * Get this situation list
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_thissituation_list
     */
    public function test_get_thissituation_list() {
        $assessment = assessment_utils::get_thissituation_list($this->students[0]->id, $this->evalplans[0]->get('id'));
        $data = $assessment->get_rows(10);
        // Check that we have one row.
        $this->assertCount(1, $data);
        $columnswithappraisergrade = array_filter(
            (array) $data[0],
            function($keyname) {
                return strstr($keyname, 'appraisergrade');
            },
            ARRAY_FILTER_USE_KEY);
        // Three assessments.
        $this->assertCount(3, $columnswithappraisergrade);
        $assessment = assessment_utils::get_thissituation_list($this->students[0]->id, $this->evalplans[1]->get('id'));
        $data = $assessment->get_rows(10);
        // Check that we have no row row.
        $this->assertCount(0, $data);
    }

    /**
     * Test get my student list
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_mystudents_list
     */
    public function test_get_mystudents_list() {
        $this->resetAfterTest();
        $this->setUser($this->assessors[0]);
        $studentlist = assessment_utils::get_mystudents_list($this->situations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have two rows.
        $this->assertCount(2, $data);
        // Check student info.
        $this->assertEquals($this->students[0]->id, $data[0]->studentid);
        $this->assertEquals(3, $data[0]->appraisalcount);

        $newuser = $this->getDataGenerator()->create_user();
        $this->setUser($newuser);
        $studentlist = assessment_utils::get_mystudents_list($this->situations[0]->get('id'));
        $data = $studentlist->get_rows(10);
        // Check that we have no row.
        $this->assertCount(0, $data);
    }

    /**
     * Test get assessment criteria list
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_assessmentcriteria_list
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
     * Test get situation for student list
     *
     * @covers \local_cveteval\local\assessment\assessment_utils::get_situations_for_student
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
