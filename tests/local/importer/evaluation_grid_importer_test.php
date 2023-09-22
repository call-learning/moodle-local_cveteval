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

use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\task\upload_default_criteria_grid;
use local_cveteval\test\importer_test_trait;

/**
 * Evaluation grid importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class evaluation_grid_importer_test extends \advanced_testcase {
    use importer_test_trait;

    /**
     * Test basic import
     *
     * @param string $filename
     * @param string $gridname
     * @param array $results
     * @param array $validationerrors
     *
     * @covers \local_cveteval\local\importer\evaluation_grid\import_helper
     * @covers \local_cveteval\local\importer\evaluation_grid\csv_data_source
     * @covers \local_cveteval\local\importer\evaluation_grid\data_importer
     * @dataProvider basic_csv_dataprovider
     */
    public function test_basic_import($filename, $gridname, $results, $validationerrors) {
        $this->resetAfterTest();
        \local_cveteval\local\persistent\history\entity::disable_history_globally();
        // Disable to speed up tests as we do not need history here.
        if (!empty($results['exception'])) {
            $this->expectException($results['exception']);
        }
        $importhelper = $this->get_import_helper('evaluation_grid', $filename);
        $this->assert_validation($importhelper, $results['haserror']);
        if ($results['haserror']) {
            $this->assert_validation_errors($validationerrors, $importhelper);
        } else {
            $importhelper->import();
            $grid = evaluation_grid_entity::get_record(['idnumber' => $gridname]);

            $allrecordsfiltered = array_map(self::class . '::extract_record_information',
                    criterion_entity::get_records(['evalgridid' => $grid->get('id')]));
            $expectedresults = array_map(
                    function($critentity) use ($grid) {
                        global $DB;
                        if (!empty($critentity['parentidnumber'])) {
                            $critentity['parentid'] = $DB->get_field(criterion_entity::TABLE, 'id', [
                                    'idnumber' => $critentity['parentidnumber'],
                                    'evalgridid' => $grid->get('id')
                            ]);
                        } else {
                            $critentity['parentid'] = 0;
                        }
                        $critentity['evalgridid'] = $grid->get('id');
                        unset($critentity['parentidnumber']);
                        return $critentity;
                    },
                    $results['imported']);
            $this->assertEquals($expectedresults, array_values($allrecordsfiltered));
        }
    }

    /**
     * Test import twice basic grid
     *
     * @covers \local_cveteval\local\importer\evaluation_grid\import_helper
     * @covers \local_cveteval\local\importer\evaluation_grid\csv_data_source
     * @covers \local_cveteval\local\importer\evaluation_grid\data_importer
     */
    public function test_import_twice() {
        $this->resetAfterTest();
        // Attempt to create the default grid twice.
        upload_default_criteria_grid::create_default_grid();
        upload_default_criteria_grid::create_default_grid();
        $grid = evaluation_grid_entity::get_record(['idnumber' => evaluation_grid_entity::DEFAULT_GRID_SHORTNAME]);
        $this->assertEquals(40, criterion_entity::count_records(['evalgridid' => $grid->get('id')]));
    }

    /**
     * Data provider for basic import
     *
     * @return array[]
     */
    public function basic_csv_dataprovider() {
        return [
                'oksample' => [
                        'filename' => 'basic_evaluation_grid.csv',
                        'gridname' => 'GRID01',
                        'results' => [
                                'haserror' => false,
                                'imported' => [
                                        [
                                                'label' => 'Savoir être',
                                                'idnumber' => 'Q001',
                                                'parentidnumber' => null,
                                                'sort' => '1',
                                        ],
                                        [
                                                'label' => 'Respect des horaires de travail',
                                                'idnumber' => 'Q002',
                                                'parentidnumber' => 'Q001',
                                                'sort' => '1',
                                        ],
                                        [
                                                'label' => 'Motivation et implication personnelle',
                                                'idnumber' => 'Q007',
                                                'parentidnumber' => null,
                                                'sort' => '2',
                                        ],
                                        [
                                                'label' => 'Motivation à apprendre (cherche à approfondir et à discuter des cas,'
                                                .' à améliorer ses compétences techniques)',
                                                'idnumber' => 'Q008',
                                                'parentidnumber' => 'Q007',
                                                'sort' => '1',
                                        ],

                                        [
                                                'label' => 'Qualités d’organisation et de travail en équipe',
                                                'idnumber' => 'Q011',
                                                'parentidnumber' => null,
                                                'sort' => '3',
                                        ],
                                ]
                        ],
                        'validationerrors' => []
                ],
                'issue with col missing' => [
                        'filename' => 'basic_evaluation_grid_with_comma.csv',
                        'gridname' => 'GRID01',
                        'results' => [
                                'haserror' => true,
                        ],
                        'validationerrors' => [
                                [
                                        'messagecode' => 'columnmissing',
                                        'linenumber' => '1',
                                        'fieldname' => 'Evaluation Grid Id',
                                        'additionalinfo' => ''
                                ]
                        ]
                ],
                'issue with wrong encoding' => [
                        'filename' => 'basic_evaluation_grid_wrong_encoding.csv',
                        'gridname' => 'GRID01',
                        'results' => [
                                'haserror' => true,
                        ],
                        'validationerrors' => [
                                [
                                        'messagecode' => 'wrongencoding',
                                        'linenumber' => '1',
                                        'fieldname' => '',
                                        'additionalinfo' => (object) ['expected' => 'utf-8', 'actual' => 'UTF-8'],
                                ]
                        ]
                ],
                'issue with parentid' => [
                        'filename' => 'basic_evaluation_grid_wrong_parentid.csv',
                        'gridname' => 'GRID01',
                        'results' => [
                                'haserror' => true,
                                'imported' => []
                        ],
                        'validationerrors' => [
                                [
                                        'messagecode' => 'wrongparentid',
                                        'linenumber' => '3',
                                        'fieldname' => 'Criterion Parent Id',
                                        'additionalinfo' => '',
                                ]
                        ]
                ],
        ];
    }
}
