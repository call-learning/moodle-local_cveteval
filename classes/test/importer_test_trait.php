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

namespace local_cveteval\test;

use core\persistent;
use dml_exception;
use local_cveteval\local\importer\base_helper;
use local_cveteval\local\importer\importid_manager;

/**
 * Importer test trait
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait importer_test_trait {
    /**
     * Used internally to get rid of values we don't want to compare with.
     *
     * @param $record
     * @return array
     */
    protected static function extract_record_information($record) {
        if ($record instanceof persistent) {
            $rec = (array) $record->to_record();
        } else {
            $rec = (array) $record;
        }
        unset($rec['id']);
        unset($rec['timecreated']);
        unset($rec['timemodified']);
        unset($rec['usermodified']);
        return $rec;
    }

    /**
     * Assert file can be imported and checked.
     */
    public function assert_validation(base_helper $importhelper, $shouldhaveerror = null, $printederrormessage = '') {
        $haserror = !$importhelper->get_processor()->validate();
        $this->assertEquals($shouldhaveerror, $haserror, $printederrormessage);
    }

    /**
     * Get import helper
     *
     * @param string $type
     * @param string $filename
     * @return mixed
     */
    public function get_import_helper($type, $filename) {
        global $CFG;
        $importidmanager = new importid_manager();
        $importid = $importidmanager->get_importid();
        $importhelperclass = "\\local_cveteval\\local\\importer\\{$type}\\import_helper";
        return new $importhelperclass(
                $CFG->dirroot . '/local/cveteval/tests/fixtures/importer/' . $filename, $importid, 'semicolon');
    }

    /**
     * Assert errors are present
     *
     * @param $expectederrors
     * @param base_helper $importhelper
     */
    public function assert_validation_errors($expectederrors, base_helper $importhelper) {
        $validationvalues = array_map(
                function($record) {
                    $rec = (array) $record->to_record();
                    $rec = array_intersect_key($rec, array_flip(['messagecode', 'linenumber', 'fieldname', 'additionalinfo']));
                    $rec['additionalinfo'] = json_decode($rec['additionalinfo']);
                    // Remove debug info.
                    if (isset($rec['additionalinfo']->info)) {
                        $rec['additionalinfo'] = $rec['additionalinfo']->info;
                    }
                    return $rec;
                },
                $importhelper->get_processor()->get_validation_log()
        );
        $this->assertEquals(array_values($expectederrors), array_values($validationvalues));
    }
}
