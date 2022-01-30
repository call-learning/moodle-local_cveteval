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

namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use core_user;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\history_model\entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\test\test_utils;

global $CFG;

require_once($CFG->libdir . '/externallib.php');

/**
 * API tests
 *
 * @package     local_cltools
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_criterion_test extends advanced_testcase {

    /**
     * setUp
     */
    public function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        test_utils::setup_from_shortsample();
    }

    /**
     * Test an API function
     */
    public function test_get_get_criterion() {
        $this->setAdminUser();
        $criteria = criterion::get();
        $this->assertNotEmpty($criteria);
        $this->assertCount(80, $criteria);
        $currentgrid = \local_cveteval\local\persistent\evaluation_grid\entity::get_record(['idnumber' => 'GRID01']);
        $criteria = criterion::get(json_encode(['evalgridid' => $currentgrid->get('id')]));
        // We retrieve only criterion related to the current grid here.
        $this->assertEquals(
            ['Q001', 'Q002', 'Q003', 'Q004', 'Q005', 'Q006', 'Q007', 'Q008', 'Q009', 'Q010', 'Q011', 'Q012', 'Q013', 'Q014', 'Q015',
                'Q016', 'Q017', 'Q018', 'Q019', 'Q020', 'Q021', 'Q022', 'Q023', 'Q024', 'Q025', 'Q026', 'Q027', 'Q028', 'Q029',
                'Q030', 'Q031', 'Q032', 'Q033', 'Q034', 'Q035', 'Q036', 'Q037', 'Q038', 'Q039', 'Q040'],
            array_values(array_map(function($s) {
                return $s->idnumber;
            }, $criteria)));

    }

}
