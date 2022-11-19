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
use local_cveteval\test\test_utils;

global $CFG;

/**
 * API tests
 *
 * @package     local_cveteval
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_utils_test extends advanced_testcase {

    /**
     * setUp
     */
    public function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        test_utils::setup_from_shortsample();
    }

    /**
     * Test query function
     *
     * @param string $query
     * @param array $expected
     * @covers \local_cveteval\local\external\external_utils::query_entities
     * @dataProvider situation_query_data_feeder
     */
    public function test_get_get_situations($query, $expected) {
        $this->setAdminUser();
        $query = json_decode($query);
        $situations = external_utils::query_entities('situation', $query);
        $this->assertNotEmpty($situations);
        // We retrieve all situations here.
        $situationsn = array_values(array_map(
                function($s) {
                    return $s->idnumber;
                }, $situations));

        $this->assertEquals($expected, $situationsn);

    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function situation_query_data_feeder() {
        return [
                'no query' => [
                        'query' => '{}',
                        'expected' => ['TMG', 'TMI', 'TUS']
                ],
                'with idnumber' => [
                        'query' => '{ "idnumber": "TMG"}',
                        'expected' => ['TMG']
                ],
                'with several idnumbers' => [
                        'query' => '{ "idnumber": { "operator": "in" , "values": ["TMG", "TUS"]}}',
                        'expected' => ['TMG', "TUS"]
                ]
        ];
    }

}
