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

global $CFG;

require_once($CFG->dirroot. '/local/cveteval/tests/local/assessment/assessment_utils_test.php');

use advanced_testcase;
use local_cveteval\local\assessment\assessment_utils_test;
use local_cveteval\test\assessment_test_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Same tests as assessment_utils_test but with history enabled
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_with_history_enabled_assessment_test extends assessment_utils_test {
    public function setUp() {
        advanced_testcase::setUp();
        $this->resetAfterTest();
        $this->create_history();
        [$this->criteria, $this->situations, $this->evalplans, $this->students, $this->assessors, $this->appraisals] =
            $this->set_up($this->get_sample_with_assessments());
        $this->students = array_values($this->students);
        $this->evalplans = array_values($this->evalplans);
        $this->situations = array_values($this->situations);
        $this->appraisals = array_values($this->appraisals);
        $this->assessors = array_values($this->assessors);
    }
}