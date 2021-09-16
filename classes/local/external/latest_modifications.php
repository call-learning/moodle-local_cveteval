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

use context_system;
use dml_exception;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use external_warnings;
use moodle_exception;

/**
 * Class latest_modifications
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class latest_modifications extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'latestmodifications' => new external_value(PARAM_INT, 'latest modification time'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Return the current role for the user
     */
    public static function execute($entitytype, $query = null) {
        static::validate_parameters(static::execute_parameters(), array(
            'entitytype' => $entitytype, 'query' => $query));
        $latestmodifications = 0;
        $warnings = [];
        try {
            static::validate_context(context_system::instance());
            $latestmodifications = static::get_entity_latest_modifications($entitytype, $query);
            if ($latestmodifications < 0) {
                $warnings[] = [
                    'item' => $entitytype,
                    'warningcode' => 'nolatestmodifs',
                    'message' => get_string('api:nolatestmodifs', 'local_cveteval')
                ];
            }
        } catch (moodle_exception $e) {
            $warnings[] = [
                'item' => $entitytype,
                'warningcode' => 'generalerror',
                'message' => get_string('api:generalerror', 'local_cveteval', $e->getMessage())
            ];
        }
        return
            [
                'latestmodifications' => $latestmodifications,
                'warnings' => $warnings
            ];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'entitytype' => new external_value(PARAM_ALPHAEXT, 'the entity to look for'),
                'query' => new external_value(PARAM_NOTAGS, 'query as json {field:value, field:value}',
                    VALUE_DEFAULT,
                    '{}'),
            )
        );
    }

    /**
     * @param $entitytype
     * @param string $queryjson
     * @return false|int|mixed
     * @throws dml_exception
     */
    public static function get_entity_latest_modifications($entitytype, $queryjson) {
        $query = [];
        if ($queryjson) {
            $query = json_decode($queryjson);
        }
        $latestmodifs = external_utils::query_entities(base_get_entity::MOBILE_ENTITY_MATCHER[$entitytype],
            $query, "MAX(e.timemodified) AS time");
        if ($latestmodifs && count($latestmodifs) > 0) {
            $latestmodif = reset($latestmodifs);
            return intval($latestmodif->time);
        }
        return -1;
    }
}
