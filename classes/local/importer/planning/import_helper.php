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
use tool_importer\importer;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\group\entity as group_entity;

class import_helper {
    /**
     * Import the csv file in the given path
     *
     * @param $csvpath
     * @return bool
     * @throws \dml_exception
     * @throws \tool_importer\importer_exception
     */
    public static function import($csvpath, $delimiter = 'comma', $progressbar = null) {
        $csvimporter = new csv_data_source($csvpath, $delimiter);
        function totimestamp($value, $columnname) {
            $date = DateTime::createFromFormat(get_string('import:dateformat', 'local_cveteval'), trim($value));
            return $date->getTimestamp();
        }

        $transformdef = array(
            'Date début' =>
                array(
                    array('to' => 'starttime', 'transformcallback' => __NAMESPACE__ . '\totimestamp')
                ),
            'Date fin' =>
                array(
                    array('to' => 'endtime', 'transformcallback' => __NAMESPACE__ . '\totimestamp')
                ),
        );

        $transformer = new \tool_importer\local\transformer\standard($transformdef);

        try {
            global $DB;
            $transaction = $DB->start_delegated_transaction();
            $importer = new importer($csvimporter,
                $transformer,
                new data_importer(null, $csvimporter->get_fields_definition()),
                $progressbar
            );
            $importer->import();
            // Send an event after importation.
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath));
            $event = \local_cveteval\event\planning_imported::create($eventparams);
            $event->trigger();
            $transaction->allow_commit();
            return true;
        } catch (\moodle_exception $e) {
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath, 'error' => $e->getMessage()));
            $event = \local_cveteval\event\planning_imported::create($eventparams);
            $event->trigger();
            if (defined('CLI_SCRIPT')) {
                cli_writeln($e->getMessage());
                cli_writeln($e->getTraceAsString());
            }
            return false;
        }
    }

    /**
     * Cleanup previously imported Clinical situation
     */
    public static function cleanup() {
        foreach (planning_entity::get_records() as $planning) {
            $planning->delete();
        }
        // Delete unreferenced groups.
        foreach (group_entity::get_records() as $group) {
            if (!planning_entity::record_exists_select("groupid = :groupid", array('groupid' => $group->get('id')))) {
                $group->delete();
            }
        }
    }
}