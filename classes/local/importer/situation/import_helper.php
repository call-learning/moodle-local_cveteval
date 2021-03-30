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
 * Clinical Situation Import process
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\situation;
defined('MOODLE_INTERNAL') || die();

use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use tool_importer\importer;

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
        function trimmeduppercase($value, $columnname) {
            return trim(strtoupper($value));
        }

        function trimmed($value, $columnname) {
            return trim($value);
        }

        function toint($value, $columnname) {
            return intval($value);
        }

        function toevalgridid($value, $columnname) {
            static $gridmatch = [];
            $trimmedval = trim($value);
            if (empty($value)) {
                return 0;
            }
            if (!empty($gridmatch[$trimmedval])) {
                return $gridmatch[$trimmedval];
            } else {
                $grid = evaluation_grid_entity::get_record(array('idnumber' => $trimmedval));
                if (!$grid) {
                    return 0;
                }
                $gridmatch[$value] = (int) $grid->get('id');
                return $gridmatch[$value];
            }
        }

        $transformdef = array(
            'Nom' =>
                array(
                    array('to' => 'title', 'transformcallback' => __NAMESPACE__ . '\trimmed')
                ),
            'Nom court' =>
                array(
                    array('to' => 'idnumber', 'transformcallback' => __NAMESPACE__ . '\trimmeduppercase')
                ),
            'Description' =>
                array(
                    array('to' => 'description')
                ),
            'Responsable' =>
                array(
                    array('to' => 'assessors', 'concatenate')
                ),
            'Evaluateurs' =>
                array(
                    array('to' => 'assessors')
                ),
            'Observateurs' =>
                array(
                    array('to' => 'appraisers')
                ),
            'Appreciations' =>
                array(
                    array('to' => 'expectedevalsnb', 'transformcallback' => __NAMESPACE__ . '\toint')
                ),
            'GrilleEval' =>
                array(
                    array('to' => 'evalgridid', 'transformcallback' => __NAMESPACE__ . '\toevalgridid')
                ),
        );

        $transformer = new \tool_importer\local\transformer\standard($transformdef, ',');

        try {
            $importer = new importer($csvimporter,
                $transformer,
                new data_importer(),
                $progressbar
            );
            $importer->import();
            // Send an event after importation.
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath));
            $event = \local_cveteval\event\situation_imported::create($eventparams);
            $event->trigger();
            return true;
        } catch (\moodle_exception $e) {
            $eventparams = array('context' => \context_system::instance(),
                'other' => array('filename' => $csvpath, 'error' => $e->getMessage()));
            $event = \local_cveteval\event\situation_imported::create($eventparams);
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
        foreach (situation_entity::get_records() as $situation) {
            $roles = role_entity::get_records(array('clsituationid' => $situation->get('id')));
            if ($roles) {
                foreach ($roles as $role) {
                    $role->delete();
                }
            }
            $situation->delete();
        }
    }
}