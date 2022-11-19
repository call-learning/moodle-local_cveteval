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

/**
 * Historical util test.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_with_history_utils_test extends \advanced_testcase {

    /**
     * Test get all history models
     *
     * @return void
     */
    public function test_get_all_history_model() {
        $classes = model_with_history_util::get_all_entity_class_with_history();
        $this->assertEquals(array (
             'local_cveteval\\local\\persistent\\criterion\\entity',
             'local_cveteval\\local\\persistent\\evaluation_grid\\entity',
            'local_cveteval\\local\\persistent\\group\\entity',
            'local_cveteval\\local\\persistent\\group_assignment\\entity',
            'local_cveteval\\local\\persistent\\planning\\entity',
            'local_cveteval\\local\\persistent\\role\\entity',
            'local_cveteval\\local\\persistent\\situation\\entity',
        ), array_values($classes));
    }
}
