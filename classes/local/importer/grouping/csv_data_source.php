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
 * Grouping Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\grouping;

use tool_importer\field_types;
use tool_importer\importer_exception;

defined('MOODLE_INTERNAL') || die();

/**
 * Class csv_data_source
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_data_source extends \tool_importer\local\source\csv_data_source {

    /**
     * A bit of a specific implementation for variable number of columns
     *
     * @return array
     * @throws importer_exception
     */
    public function get_fields_definition() {
        static $columns = null;

        if (!$columns) {
            $additionalcolumns = [
                'Identifiant' => field_types::TYPE_TEXT
            ];
            if (!$this->csvimporter) {
                throw new importer_exception('nocolumnsdefined', 'tool_importer', null, '');
            }
            if ($allcolumns = $this->csvimporter) {
                $allcolumns = $this->csvimporter->get_columns();
                foreach ($allcolumns as $colname) {
                    if (preg_match('/groupement.*/', strtolower($colname))) {
                        $additionalcolumns[$colname] = field_types::TYPE_TEXT;
                    }
                }
            }
            $columns = $additionalcolumns;
        }
        return $columns;
    }
}
