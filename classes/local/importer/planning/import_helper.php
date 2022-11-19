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
 * Grouping Importer process
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\planning;

use DateTime;
use local_cveteval\event\planning_imported;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\persistent\planning\entity as planning_entity;
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
        $this->importeventclass = planning_imported::class;
    }

    /**
     * Timestamp from value
     *
     * @param string $value
     * @param string $columnname
     * @return int
     */
    public static function totimestamp($value, $columnname) {
        $date = DateTime::createFromFormat(get_string('import:dateformat', 'local_cveteval'), trim($value));
        $date->setTime(1, 0);
        return $date->getTimestamp();
    }

    /**
     * Cleanup previously imported Clinical situation
     */
    public function cleanup() {
        foreach (planning_entity::get_records() as $planning) {
            $planning->delete();
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
                'Date début' =>
                        array(
                                array('to' => 'starttime', 'transformcallback' => self::class . '::totimestamp')
                        ),
                'Date fin' =>
                        array(
                                array('to' => 'endtime', 'transformcallback' => self::class . '::totimestamp')
                        ),
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
     * Create importer
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
                        get_string('planning:stats', 'local_cveteval',
                                ['plannings' => $this->importer->planningcount,
                                        'planningevents' => $this->importer->planningeventscount]
                        );
            }
        };
    }
}
