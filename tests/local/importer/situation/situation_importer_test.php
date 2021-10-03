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

use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\test\importer_test_trait;

defined('MOODLE_INTERNAL') || die();

/**
 * Situation importer test
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class situation_importer_test extends \advanced_testcase {
    use importer_test_trait;

    const EXISTING_USERS = [
        'appraiser_0001@exemple.com',
        'appraiser_0003@exemple.com',
        'appraiser_0004@exemple.com',
        'appraiser_0005@exemple.com',
        'appraiser_0006@exemple.com',
        'appraiser_0007@exemple.com',
        'appraiser_0008@exemple.com',
        'resp1@exemple.com',
        'resp2@exemple.com',
        'resp3@exemple.com',
        'obs1@exemple.com',
        'obs2@exemple.com',
        'obs3@exemple.com',
        'obs4@exemple.com',
        'obs5@exemple.com',
    ];

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
        $importhelper = $this->get_import_helper('situation', $filename);
        $this->assert_validation($importhelper, $results['haserror'], $results['exception'] ?? null);
        if ($results['haserror']) {
            $this->assert_validation_errors($validationerrors, $importhelper);
        } else {
            $importhelper->import();
            $records = situation_entity::get_records();
            $allrecordsfiltered = array_map(self::class . '::extract_record_information', $records);
            $this->assertEquals($results['imported']['situations'], array_values($allrecordsfiltered));
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
                'filename' => 'basic_situations.csv',
                'results' => [
                    'haserror' => false,
                    'imported' => [
                        'situations' => [
                            0 =>
                                [
                                    'title' => 'Consultations de médecine générale',
                                    'description' => 'Clinique des animaux de compagnie : médecine générale – médecine interne – médecine d’urgence et soins intensifs',
                                    'descriptionformat' => '1',
                                    'idnumber' => 'TMG',
                                    'expectedevalsnb' => '1',
                                    'evalgridid' => '1',
                                ],
                            [
                                'title' => 'Médecine interne',
                                'description' => 'Clinique des animaux de compagnie : médecine générale – médecine interne – médecine d’urgence et soins intensifs',
                                'descriptionformat' => '1',
                                'idnumber' => 'TMI',
                                'expectedevalsnb' => '2',
                                'evalgridid' => '1',
                            ],
                            [
                                'title' => 'Urgences-Soins intensifs',
                                'description' => 'Clinique des animaux de compagnie : médecine générale – médecine interne – médecine d’urgence et soins intensifs',
                                'descriptionformat' => '1',
                                'idnumber' => 'TUS',
                                'expectedevalsnb' => '2',
                                'evalgridid' => '1',
                            ]

                        ]
                    ],
                    'errors' => []
                ],
                'validationerrors' => []
            ],
            'missinguser' => [
                'filename' => 'basic_situations_missinguser.csv',
                'results' => [
                    'haserror' => true,
                    'imported' => [
                        'situations' => [

                        ]
                    ],
                    'errors' => []
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '3',
                        'messagecode' => 'situation:usernotfound',
                        'additionalinfo' => 'appraiser_0002@exemple.com',
                        'fieldname' => 'Observateurs',
                    ]
                ]
            ],
            'duplicateshortname' => [
                'filename' => 'basic_situations_duplicateshortname.csv',
                'results' => [
                    'haserror' => true,
                    'imported' => [
                        'situations' => [

                        ]
                    ],
                    'errors' => []
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '3',
                        'messagecode' => 'situation:duplicateshortname',
                        'additionalinfo' => 'TMG',
                        'fieldname' => 'Nom court'
                    ]
                ]
            ],
            'missingcolumns' => [
                'filename' => 'basic_situations_missingcolumn.csv',
                'results' => [
                    'haserror' => true,
                    'imported' => [
                        'situations' => [

                        ]
                    ],
                    'errors' => []
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '1',
                        'messagecode' => 'columnmissing',
                        'additionalinfo' => '',
                        'fieldname' => 'Nom court'
                    ]
                ]
            ],
            'wronggrid' => [
                'filename' => 'basic_situations_wrong_grid.csv',
                'results' => [
                    'haserror' => true,
                    'imported' => [
                        'situations' => [

                        ]
                    ],
                    'errors' => []
                ],
                'validationerrors' => [

                    [
                        'linenumber' => '2',
                        'messagecode' => 'situation:gridnotfound',
                        'additionalinfo' => 'GRIDABC',
                        'fieldname' => 'GrilleEval',
                    ],
                    [
                        'linenumber' => '3',
                        'messagecode' => 'situation:gridnotfound',
                        'additionalinfo' => 'GRID01',
                        'fieldname' => 'GrilleEval',
                    ],
                    [
                        'linenumber' => '4',
                        'messagecode' => 'situation:gridnotfound',
                        'additionalinfo' => 'GRID01',
                        'fieldname' => 'GrilleEval',
                    ],
                ]
            ],
        ];
    }
}
