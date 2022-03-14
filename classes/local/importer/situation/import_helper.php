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

use cache;
use cache_store;
use coding_exception;
use local_cveteval\event\situation_imported;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
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
        $this->importeventclass = situation_imported::class;
    }

    /**
     * To eval grid
     *
     * @param $value
     * @param $columnname
     * @return int|mixed
     * @throws coding_exception
     */
    public static function toevalgridid($value, $columnname) {
        if (empty($value)) {
            return 0;
        }
        $gridcache = cache::make_from_params(cache_store::MODE_REQUEST, 'local_cveteval', 'toevalgridcache');
        $trimmedval = trim($value);
        if ($gridcache->has($trimmedval)) {
            return $gridcache->get($trimmedval);
        } else {
            $grid = evaluation_grid_entity::get_record(array('idnumber' => $trimmedval));
            if (!$grid) {
                return 0;
            }
            $id = (int) $grid->get('id');
            $gridcache->set($trimmedval, $id);
            return $id;
        }
    }

    /**
     * Cleanup previously imported Clinical situation
     */
    public function cleanup() {
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
                'Nom' =>
                        array(
                                array('to' => 'title', 'transformcallback' => base_helper::class . '::trimmed')
                        ),
                'Nom court' =>
                        array(
                                array('to' => 'idnumber', 'transformcallback' => base_helper::class . '::trimmeduppercase')
                        ),
                'Description' =>
                        array(
                                array('to' => 'description')
                        ),
                'Responsable' =>
                        array(
                                array('to' => 'assessors', 'concatenate' => ['order' => 0])
                        ),
                'Evaluateurs' =>
                        array(
                                array('to' => 'assessors', 'concatenate' => ['order' => 1])
                        ),
                'Observateurs' =>
                        array(
                                array('to' => 'appraisers')
                        ),
                'Appreciations' =>
                        array(
                                array('to' => 'expectedevalsnb', 'transformcallback' => base_helper::class . '::toint')
                        ),
                'GrilleEval' =>
                        array(
                                array('to' => 'evalgridid', 'transformcallback' => self::class . '::toevalgridid')
                        ),
        );

        return new standard($transformdef, ',');
    }

    /**
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
                        get_string('situation:stats', 'local_cveteval',
                                ['roles' => $this->importer->rolescount, 'situations' => $this->importer->situationscount]
                        );
            }
        };
    }
}
