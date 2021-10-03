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

namespace local_cveteval\local\importer;

use local_cveteval\test\importer_test_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Grouping importer test
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grouping_importer_test extends \advanced_testcase {
    use importer_test_trait;

    const EXISTING_USERS = ['etu1@exemple.com', 'etu2@exemple.com', 'etu3@exemple.com', 'etu4@exemple.com', 'obs7@exemple.com'];

    /**
     * @dataProvider basic_csv_dataprovider
     */
    public function test_basic_import($filename, $results, $validationerrors) {
        $this->resetAfterTest();
        \local_cveteval\local\persistent\history\entity::disable_history_globally(); // Disable to speed up tests as we do not need history here.
        if (!empty($results['exception'])) {
            $this->expectException($results['exception']);
        }
        foreach (self::EXISTING_USERS as $useremail) {
            $this->getDataGenerator()->create_user(['email' => $useremail]);
        }
        $importhelper = $this->get_import_helper('grouping', $filename);
        $this->assert_validation($importhelper, $results['haserror']);
        if ($results['haserror']) {
            $this->assert_validation_errors($validationerrors, $importhelper);
        } else {
            global $DB;
            $importhelper->import();
            $records = $DB->get_records_sql('SELECT u.id, u.email, g.name
                FROM {local_cveteval_group_assign} ga
                LEFT JOIN {user} u ON u.id = ga.studentid
                LEFT JOIN {local_cveteval_group} g ON g.id = ga.groupid
                ');
            $allrecordsfiltered = array_map(self::class . '::extract_record_information', $records);
            $this->assertEquals($results['imported'], array_values($allrecordsfiltered));
        }
    }

    /**
     * Data provider for basic import
     *
     * @return array[]
     */
    public function basic_csv_dataprovider() {
        return [
            'oksample' => [
                'filename' => 'basic_grouping.csv',
                'results' => [
                    'haserror' => false,
                    'imported' => [
                        [
                            'email' => 'etu1@exemple.com',
                            'name' => 'Groupe A',
                        ],
                        [
                            'email' => 'etu2@exemple.com',
                            'name' => 'Groupe A',
                        ],
                        [
                            'email' => 'etu3@exemple.com',
                            'name' => 'Groupe B',
                        ],
                        [
                            'email' => 'etu4@exemple.com',
                            'name' => 'Groupe B',
                        ],
                        [
                            'email' => 'obs7@exemple.com',
                            'name' => 'Groupe B',
                        ],
                    ],
                    'errors' => []
                ],
                'validationerrors' => []
            ],
            'oksample with no names' => [
                'filename' => 'basic_grouping_noname.csv',
                'results' => [
                    'haserror' => false,
                    'imported' => [
                        [
                            'email' => 'etu1@exemple.com',
                            'name' => 'Groupe A',
                        ],
                        [
                            'email' => 'etu2@exemple.com',
                            'name' => 'Groupe A',
                        ],
                        [
                            'email' => 'etu3@exemple.com',
                            'name' => 'Groupe B',
                        ],
                        [
                            'email' => 'etu4@exemple.com',
                            'name' => 'Groupe B',
                        ],
                        [
                            'email' => 'obs7@exemple.com',
                            'name' => 'Groupe B',
                        ],
                    ]
                ],
                'validationerrors' => []
            ],
            'missingusers' => [
                'filename' => 'basic_grouping_missinguser.csv',
                'results' => [
                    'haserror' => true,
                    'errors' => [],
                    'imported' => []
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '2',
                        'messagecode' => 'grouping:usernotfound',
                        'additionalinfo' => 'obs8@exemple.com',
                        'fieldname' => 'email'
                    ]
                ]
            ],
            'issue with encoding' => [
                'filename' => 'basic_grouping_wrong_encoding.csv',
                'results' => [
                    'haserror' => true,
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '1',
                        'messagecode' => 'wrongencoding',
                        'additionalinfo' => (object) ['expected' => 'utf-8', 'actual' => 'UTF-8'],
                        'fieldname' => '',
                    ]
                ]
            ],

        ];
    }
}
