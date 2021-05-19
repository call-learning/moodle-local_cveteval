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
defined('MOODLE_INTERNAL') || die();

use DateTime;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\group\entity as group_entity;

class import_helper extends base_helper {
    /**
     * import_helper constructor.
     *
     * @param $csvpath
     * @param $importid
     * @param string $delimiter
     * @param string $encoding
     * @param null $progressbar
     * @throws \tool_importer\importer_exception
     */
    public function __construct($csvpath, $importid, $delimiter = 'semicolon', $encoding = 'utf-8', $progressbar = null) {
        parent::__construct($csvpath, $importid, $delimiter, $encoding, $progressbar);
        $this->importeventclass = \local_cveteval\event\planning_imported::class;
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
     * @param $csvpath
     * @param $delimiter
     * @param $encoding
     * @return \tool_importer\data_source
     */
    protected function create_csv_datasource($csvpath, $delimiter, $encoding) {
        return new csv_data_source($csvpath, $delimiter, $encoding);
    }
    /**
     * @return \tool_importer\data_transformer
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

        $transformer = new \tool_importer\local\transformer\standard($transformdef);
        return $transformer;
    }
    /**
     * @return \tool_importer\data_importer
     */
    protected function create_data_importer() {
        return new data_importer($this->csvimporter->get_fields_definition());
    }

    public static function totimestamp($value, $columnname) {
        $date = DateTime::createFromFormat(get_string('import:dateformat', 'local_cveteval'), trim($value));
        $date->setTime(1,0,0,0);
        return $date->getTimestamp();
    }

}