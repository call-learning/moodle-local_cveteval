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
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use local_cltools\local\crud\entity_table;
use tool_importer\local\import_log;

/**
 * Persistent import log
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends entity_table {
    protected static $persistentclass = import_log::class;

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws coding_exception
     */
    protected static function define_properties() {
        return array(
            'timecreated' => array(
                'type' => PARAM_INT,
                'default' => '',
                'format' => [
                    'type' => 'datetime'
                ]
            ),
            'importid' => array(
                'type' => PARAM_INT,
                'null' => NULL_ALLOWED,
                'default' => 0
            )
        );
    }

    /**
     * @return array
     */
    public function get_fields_definition() {
        $columns = parent::get_fields_definition();
        // This is a temporary hack so we can set the width or other params
        // for this column.
        $columns['additionalinfo']->additionalParams = json_encode(
            (object) [
                'widthGrow' => 6
            ]
        );
        return $columns;
    }
}
