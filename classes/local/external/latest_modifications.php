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
 * External services
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();

use external_function_parameters;
use external_single_structure;
use external_value;

class latest_modifications extends \external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_latest_modifications_parameters() {
        return new external_function_parameters(
            array(
                'entitytype' => new external_value(PARAM_ALPHAEXT, 'the entity to look for'),
                'contextid'=> new external_value(PARAM_INT, 'the context id if needed', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function get_latest_modifications_returns() {
        return new external_single_structure(
            array(
                'latestmodifications' => new external_value(PARAM_INT, 'latest modification time'),
            )
        );
    }

    /**
     * Return the current role for the user
     */
    public static function get_latest_modifications($entitytype, $contextid) {
        $params = self::validate_parameters(self::get_latest_modifications_parameters(), array(
            'entitytype' => $entitytype, 'contextid' => $contextid));
        self::validate_context(\context_system::instance());
        return static::get_entity_latest_modifications($entitytype, $contextid);
    }

    /**
     * @param $entitytype
     * @param $contextid
     * @throws \dml_exception
     */
    public static function get_entity_latest_modifications($entitytype, $contextid) {
        global $DB;
        $classname = '\\local_cveteval\\local\\persistent\\'.$entitytype;
        if (class_exists('\\local_cveteval\\local\\persistent\\'.$entitytype)) {
            return $DB->get_record_sql("SELECT MAX(timemodified) FROM {".$classname::TABLE."}");
        }
        return 0;
    }
}
