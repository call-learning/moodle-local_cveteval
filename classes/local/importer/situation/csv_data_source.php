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
 * Clinical Situation Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_cveteval\local\importer\situation;
use tool_importer\field_types;

defined('MOODLE_INTERNAL') || die();

class csv_data_source extends \tool_importer\local\source\csv_data_source {
    /**
     *
     * @return array
     */
    public function get_fields_definition() {
        return array(
            'Nom' => field_types::TYPE_TEXT,
            'Description' => field_types::TYPE_TEXT,
            'Nom court' => field_types::TYPE_TEXT,
            'Responsable' => field_types::TYPE_TEXT,
            'Evaluateurs' => field_types::TYPE_TEXT,
            'Observateurs' => field_types::TYPE_TEXT,
            'Appreciations' => field_types::TYPE_INT,
            'GrilleEval' => field_types::TYPE_TEXT
        );
    }
}
