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

namespace local_cveteval\local\importer\grouping;
defined('MOODLE_INTERNAL') || die();

use local_cveteval\event\grouping_imported;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use tool_importer\data_source;
use tool_importer\data_transformer;
use tool_importer\local\exceptions\importer_exception;
use tool_importer\local\transformer\standard;
use tool_importer\processor;

class import_helper extends base_helper {
    /**
     * import_helper constructor.
     *
     * @param $csvpath
     * @param $importid
     * @param string $filename
     * @param string $delimiter
     * @param string $encoding
     * @param null $progressbar
     * @throws importer_exception
     */
    public function __construct($csvpath, $importid, $filename = '', $delimiter = 'semicolon', $encoding = 'utf-8',
        $progressbar = null) {
        parent::__construct($csvpath, $importid, $filename, $delimiter, $encoding, $progressbar);
        $this->importeventclass = grouping_imported::class;
    }

    /**
     * Cleanup previously imported grouping
     */
    public function cleanup() {
        foreach (group_assignment_entity::get_records() as $ga) {
            $ga->delete();
        }
        // Delete unreferenced groups.
        foreach (group_entity::get_records() as $group) {
            if (!planning_entity::record_exists_select("groupid = :groupid", array('groupid' => $group->get('id')))) {
                $group->delete();
            }
        }
    }

    /**
     * Create the CSV Datasource
     *
     * @param $csvpath
     * @param $delimiter
     * @param $encoding
     * @param $filename
     * @return data_source
     */
    protected function create_csv_datasource($csvpath, $delimiter, $encoding, $filename) {
        return new csv_data_source($csvpath, $delimiter, $encoding, $filename);
    }

    /**
     * @return data_transformer
     */
    protected function create_transformer() {
        $transformdef = array(
            'Identifiant' =>
                array(
                    array('to' => 'email', 'transformcallback' => self::class . '::to_email')
                ),
        );

        $transformer = new standard($transformdef);
        return $transformer;
    }

    /**
     * @return \tool_importer\data_importer
     */
    protected function create_data_importer() {
        return new data_importer();
    }

    /**
     * To email
     *
     * @param $value
     * @param $columnname
     * @return string
     */
    public static function to_email($value, $columnname) {
        return clean_param(trim($value), PARAM_EMAIL);
    }
    /**
     * Create processor
     *
     * @param csv_data_source $csvsource
     * @param data_transformer $transformer
     * @param data_importer $dataimporter
     * @param $progressbar
     * @param $importid
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
             * @return string
             */
            public function get_displayable_stats() {
                return
                    get_string('grouping:stats', 'local_cveteval',
                        ['groups' => $this->importer->groupcount, 'groupassignments' => $this->importer->groupassignmentcount ]
                    );
            }
        };
    }
}
