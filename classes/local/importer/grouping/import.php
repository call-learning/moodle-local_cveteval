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

use DateTime;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use tool_importer\importer;

class import {
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
        function trimmed($value, $columnname) {
            return trim($value);
        }
        function toint($value, $columnname) {
            return intval($value);
        }

        $transformdef = array(
            'Identifiant' =>
                array(
                    array('to' => 'email', 'transformcallback' => __NAMESPACE__ . '\trimmed')
                ),
        );

        $transformer = new \tool_importer\local\transformer\standard($transformdef);

        try {
            $importer = new importer($csvimporter,
                $transformer,
                new data_importer(null,  $csvimporter->get_fields_definition()),
                $progressbar
            );
            $importer->import();
            // Send an event after importation.
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath));
            $event = \local_cveteval\event\grouping_imported::create($eventparams);
            $event->trigger();
            return true;
        } catch (\moodle_exception $e) {
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath, 'error' => $e->getMessage()));
            $event = \local_cveteval\event\grouping_imported::create($eventparams);
            $event->trigger();
            if (defined('CLI_SCRIPT')) {
                cli_writeln($e->getMessage());
                cli_writeln($e->getTraceAsString());
            }
            return false;
        }
    }

    /**
     * Cleanup previously imported grouping
     */
    public static function cleanup() {
        foreach (group_assignment_entity::get_records() as $ga) {
            $ga->delete();
        }
    }
}