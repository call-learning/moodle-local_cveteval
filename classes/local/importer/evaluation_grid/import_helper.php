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
 * Evaluation Grid Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\evaluation_grid;

use local_cveteval\event\evaluation_grid_imported;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use tool_importer\data_source;
use tool_importer\data_transformer;
use tool_importer\local\transformer\standard;
use tool_importer\processor;

/**
 * Import helper
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_helper extends base_helper {
    /**
     * import_helper constructor.
     *
     * @param string $csvpath
     * @param int $importid
     * @param string $filename
     * @param string $delimiter
     * @param string $encoding
     * @param null $progressbar
     */
    public function __construct($csvpath, $importid, $filename = '', $delimiter = 'semicolon', $encoding = 'utf-8',
            $progressbar = null) {
        parent::__construct($csvpath, $importid, $filename, $delimiter, $encoding, $progressbar);
        $this->importeventclass = evaluation_grid_imported::class;
    }

    /**
     * Cleanup previously imported evaluation grid
     */
    public function cleanup() {
        foreach (evaluation_grid_entity::get_records() as $ga) {
            if ($ga->get('idnumber') != evaluation_grid_entity::DEFAULT_GRID_SHORTNAME) {
                foreach (criterion_entity::get_records(['evalgridid' => $ga->get('id')]) as $qa) {
                    $qa->delete();
                }
                $ga->delete();
            }
        }
    }

    /**
     * Create the CSV Datasource
     *
     * @param string $csvpath
     * @param string $delimiter
     * @param string $encoding
     * @param string $filename
     * @return data_source
     */
    protected function create_csv_datasource($csvpath, $delimiter, $encoding, $filename) {
        return new csv_data_source($csvpath, $delimiter, $encoding, $filename);
    }

    /**
     * Create transformer
     *
     * @return data_transformer
     */
    protected function create_transformer() {
        $transformdef = array(
                'Evaluation Grid Id' =>
                        array(
                                array('to' => 'evalgridid', 'transformcallback' => base_helper::class . '::trimmed')
                        ),
                'Criterion Id' =>
                        array(
                                array('to' => 'idnumber', 'transformcallback' => base_helper::class . ':trimmed')
                        ),
                'Criterion Parent Id' =>
                        array(
                                array('to' => 'parentidnumber', 'transformcallback' => base_helper::class . ':trimmed')
                        ),
                'Criterion Label' =>
                        array(
                                array('to' => 'label', 'transformcallback' => base_helper::class . ':trimmed')
                        )
        );
        return new standard($transformdef);
    }

    /**
     * Create importer
     *
     * @return \tool_importer\data_importer
     */
    protected function create_data_importer() {
        return new data_importer();
    }

    /**
     * Create processor
     *
     * @param csv_data_source $csvsource
     * @param data_transformer $transformer
     * @param data_importer $dataimporter
     * @param object $progressbar
     * @param int $importid
     * @return processor
     */
    protected function create_processor($csvsource, $transformer, $dataimporter,
            $progressbar, $importid) {
        return new class($csvsource,
                $transformer,
                $dataimporter,
                $progressbar,
                $importid
        ) extends processor {
            /**
             * Get statistics in a displayable (HTML) format
             *
             * @return string
             */
            public function get_displayable_stats() {
                return
                        get_string('evaluationgrid:stats', 'local_cveteval',
                                ['criterions' => $this->importer->criterioncount]
                        );
            }
        };
    }
}
