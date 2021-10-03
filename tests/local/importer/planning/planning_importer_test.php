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
use tool_importer\local\exceptions\importer_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Planning importer test
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class planning_importer_test extends \advanced_testcase {
    use importer_test_trait;

    const EXISTING_USERS = [
        'appraiser_0001@exemple.com',
        'appraiser_0002@exemple.com',
        'resp1@exemple.com',
        'resp2@exemple.com',
        'resp3@exemple.com',
        'obs1@exemple.com',
        'obs2@exemple.com',
        'obs3@exemple.com',
        'etu1@exemple.com',
        'etu2@exemple.com',
        'etu3@exemple.com',
        'etu4@exemple.com',
    ];

    /**
     * @dataProvider basic_csv_dataprovider
     */
    public function test_basic_import($filename, $results, $validationerrors) {
        $this->resetAfterTest();
        \local_cveteval\local\persistent\history\entity::disable_history_globally(); // Disable to speed up tests as we do not need history here.
        foreach (self::EXISTING_USERS as $useremail) {
            $this->getDataGenerator()->create_user(['email' => $useremail]);
        }
        $dependencies = [
            'situation' => 'basic_planning_situations.csv',
            'grouping' => 'basic_planning_grouping.csv',
        ];
        // First import groups and situations.
        foreach ($dependencies as $helpername => $helperfilename) {
            $importhelper = $this->get_import_helper($helpername, $helperfilename);
            $importhelper->import();
        }

        $importhelper = $this->get_import_helper('planning', $filename);
        $this->assert_validation($importhelper, $results['haserror']);
        if ($results['haserror']) {
            $this->assert_validation_errors($validationerrors, $importhelper);
        } else {
            global $DB;
            $importhelper->import();
            $records = $DB->get_records_sql('SELECT p.id, g.name, cl.title, p.starttime, p.endtime
                FROM {local_cveteval_evalplan} p
                LEFT JOIN {local_cveteval_group} g ON g.id = p.groupid
                LEFT JOIN {local_cveteval_clsituation} cl ON cl.id = p.clsituationid
                ORDER BY  p.id');
            $allrecordsfiltered = array_map(self::class . '::extract_record_information', $records);
            $allrecordsfiltered = array_map(function($rec) {
                $rec['starttime'] = userdate($rec['starttime'], get_string('strftimedate'));
                $rec['endtime'] = userdate($rec['endtime'], get_string('strftimedate'));
                unset($rec['id']);
                return $rec;
            }, $allrecordsfiltered);
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
                'filename' => 'basic_planning.csv',
                'results' => [
                    'haserror' => false,
                    'imported' => [
                        [
                            'name' => 'Groupe A',
                            'title' => 'Consultations de médecine générale',
                            'starttime' => '24 May 2021',
                            'endtime' => '30 May 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Urgences-Soins intensifs',
                            'starttime' => '24 May 2021',
                            'endtime' => '30 May 2021',
                        ],
                        [
                            'name' => 'Groupe A',
                            'title' => 'Médecine interne',
                            'starttime' => '31 May 2021',
                            'endtime' => '6 June 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Consultations de médecine générale',
                            'starttime' => '31 May 2021',
                            'endtime' => '6 June 2021',
                        ],
                        [
                            'name' => 'Groupe A',
                            'title' => 'Urgences-Soins intensifs',
                            'starttime' => '7 June 2021',
                            'endtime' => '13 June 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Médecine interne',
                            'starttime' => '7 June 2021',
                            'endtime' => '13 June 2021',
                        ],
                        [
                            'name' => 'Groupe A',
                            'title' => 'Consultations de médecine générale',
                            'starttime' => '14 June 2021',
                            'endtime' => '20 June 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Urgences-Soins intensifs',
                            'starttime' => '14 June 2021',
                            'endtime' => '20 June 2021',
                        ],
                        [
                            'name' => 'Groupe A',
                            'title' => 'Médecine interne',
                            'starttime' => '21 June 2021',
                            'endtime' => '27 June 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Consultations de médecine générale',
                            'starttime' => '21 June 2021',
                            'endtime' => '27 June 2021',
                        ],
                        [
                            'name' => 'Groupe A',
                            'title' => 'Urgences-Soins intensifs',
                            'starttime' => '28 June 2021',
                            'endtime' => '4 July 2021',
                        ],
                        [
                            'name' => 'Groupe B',
                            'title' => 'Médecine interne',
                            'starttime' => '28 June 2021',
                            'endtime' => '4 July 2021',
                        ],
                    ],
                    'errors' => []
                ],
                'validationerrors' => []
            ],
            'issue with overlapping dates' => [
                'filename' => 'basic_planning_overlap_date.csv',
                'results' => [
                    'haserror' => true,
                    'imported' => [
                    ]
                ],
                'validationerrors' => [
                    [
                        'linenumber' => '3',
                        'messagecode' => 'planning:dateoverlaps',
                        'additionalinfo' => (object) [
                            "prevrowindex" => 2,
                            "previousstartdate" => "24/05/21",
                            "previousenddate" => "30/05/21",
                            "currentstartdate" => "25/05/21",
                            "currentenddate" => "6/06/21"
                        ],
                        'fieldname' => 'Date début',
                    ],
                    [
                        'linenumber' => '5',
                        'messagecode' => 'planning:dateoverlaps',
                        'additionalinfo' =>
                            (object) [
                                "prevrowindex" => 4,
                                "previousstartdate" => "7/06/21",
                                "previousenddate" => "13/06/21",
                                "currentstartdate" => "6/06/21",
                                "currentenddate" => "12/06/21"
                            ],
                        'fieldname' => 'Date fin',
                    ],
                ]
            ],
            'missing group' => [
                'filename' => 'basic_planning_missing_group.csv',
                'results' => [
                    'haserror' => true,
                    'errors' => [],
                    'imported' => []
                ],
                'validationerrors' => [
                    [

                        'linenumber' => '1',
                        'messagecode' => 'columnmissing',
                        'additionalinfo' => '',
                        'fieldname' => 'Groupe B',
                    ]
                ]
            ],
            'missing situation' => [
                'filename' => 'basic_planning_missing_situation.csv',
                'results' => [
                    'haserror' => true,
                    'exception' => importer_exception::class
                ],
                'validationerrors' => [

                    [
                        'linenumber' => '2',
                        'messagecode' => 'planning:situationnotfound',
                        'additionalinfo' => 'TAG',
                        'fieldname' => 'Groupe B',
                    ],
                    [
                        'linenumber' => '3',
                        'messagecode' => 'planning:situationnotfound',
                        'additionalinfo' => 'TEG',
                        'fieldname' => 'Groupe B',
                    ],

                ]
            ],

        ];
    }
}
