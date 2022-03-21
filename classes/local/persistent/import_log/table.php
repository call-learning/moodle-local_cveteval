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
 * Import log
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\import_log;

use coding_exception;
use local_cltools\local\crud\entity_table;
use moodle_exception;
use tool_importer\local\log_levels;

/**
 * Persistent import log
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends entity_table {
    protected static $persistentclass = entity::class;

    /**
     * @return array
     */
    public function get_fields_definition() {
        $columns = parent::get_fields_definition();
        // This is a temporary hack so we can set the width or other params
        // for this column.
        $columns['information']->additionalParams = json_encode(
                (object) [
                        'widthGrow' => 6
                ]
        );
        return $columns;
    }

    /**
     * Format the origin field
     *
     * @param $row
     * @return string
     */
    protected function col_origin($row) {
        return basename($row->origin);
    }

    /**
     * Format the origin field
     *
     * @param $row
     * @return string
     * @throws moodle_exception
     */
    protected function col_information($row) {
        $addinfo = json_decode($row->additionalinfo) ?? '';
        if (is_object($addinfo)) {
            $addinfo = $addinfo->info ?? '';
        } else {
            $addinfo = $row->additionalinfo;
        }
        $message = get_string($row->messagecode, $row->module, $addinfo);
        return $message;
    }

    /**
     * Format the level field
     *
     * @param $row
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function col_level($row) {
        return strtoupper(strtoupper(log_levels::to_displayable_string($row->level, 'local_cveteval')));
    }
}
