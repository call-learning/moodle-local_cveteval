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
 * Base import helper
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer;
defined('MOODLE_INTERNAL') || die();

use context_system;
use dml_exception;
use local_cveteval\local\importer\grouping\csv_data_source;
use moodle_exception;
use tool_importer\data_importer;
use tool_importer\data_source;
use tool_importer\data_transformer;
use tool_importer\importer;
use tool_importer\importer_exception;

abstract class base_helper {

    /**
     * @var string $csvpath
     */
    protected $csvpath = null;
    /**
     * @var csv_data_source |null current importer
     */
    protected $csvimporter = null;
    /**
     * @var importer|null current importer
     */
    protected $importer = null;
    /**
     * Class used to send events
     *
     * @var null
     */
    protected $importeventclass = null;
    /**
     * @var data_transformer
     */
    protected $transformer;
    /**
     * @var data_importer
     */
    protected $dataimporter;

    /**
     * import_helper constructor.
     *
     * @param $csvpath
     * @param $importid
     * @param string $delimiter
     * @param string $encoding
     * @param null $progressbar
     * @throws importer_exception
     */
    public function __construct($csvpath, $importid, $delimiter = 'semicolon', $encoding = 'utf-8', $progressbar = null) {
        $this->csvimporter = $this->create_csv_datasource($csvpath, $delimiter, $encoding);
        $this->transformer = $this->create_transformer();
        $this->dataimporter = $this->create_data_importer();
        $this->csvpath = $csvpath;
        $this->importer = new importer($this->csvimporter,
            $this->transformer,
            $this->dataimporter,
            $progressbar,
            $importid
        );
    }

    /**
     * @param $csvpath
     * @param $delimiter
     * @param $encoding
     * @return data_source
     */
    abstract protected function create_csv_datasource($csvpath, $delimiter, $encoding);

    /**
     * @return data_transformer
     */
    abstract protected function create_transformer();

    /**
     * @return data_importer
     */
    abstract protected function create_data_importer();

    public static function trimmed($value, $columnname) {
        return trim($value);
    }

    public static function toint($value, $columnname) {
        return intval($value);
    }

    public static function trimmeduppercase($value, $columnname) {
        return trim(strtoupper($value));
    }

    /**
     * Import the csv file in the given path
     *
     * @return bool success or failure
     * @throws dml_exception
     * @throws importer_exception
     */
    public function import() {
        try {
            global $DB;
            $transaction = $DB->start_delegated_transaction();
            $this->importer->import();
            // Send an event after importation.
            $eventparams = array('context' => context_system::instance(),
                'other' => array('filename' => $this->csvpath));
            $event = $this->importeventclass::create($eventparams);
            $event->trigger();
            $transaction->allow_commit();
            return true;
        } catch (moodle_exception $e) {
            $eventparams = array('context' => context_system::instance(),
                'other' => array('filename' => $this->csvpath, 'error' => $e->getMessage()));
            $event = $this->importeventclass::create($eventparams);
            $event->trigger();
            if (defined('CLI_SCRIPT')) {
                cli_writeln($e->getMessage());
                cli_writeln($e->getTraceAsString());
            }
            return false;
        }
    }

    /**
     * @return int|mixed
     */
    public function get_row_imported_count() {
        return $this->importer->get_row_imported_count();
    }

    /**
     * @return mixed
     */
    public function get_total_row_count() {
        return $this->importer->get_total_row_count();
    }

}
