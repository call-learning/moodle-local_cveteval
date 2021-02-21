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
use external_multiple_structure;
use external_single_structure;
use external_value;

use \local_cveteval\local\persistent\role\entity as role_entity;
use \local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use \local_cveteval\local\persistent\appraisal_criterion\entity as app_crit_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use stdClass;

class criteria extends \external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_criteria_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function get_criteria_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'id of the criteria'),
                        'label' => new external_value(PARAM_TEXT, 'label of the eval grid'),
                        'sort' => new external_value(PARAM_TEXT, 'label of the eval grid'),
                        'gridid' => new external_value(PARAM_INT, 'id of the eval grid'),
                        'subcriteria' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'id' => new external_value(PARAM_INT, 'id of the criteria'),
                                    'label' => new external_value(PARAM_TEXT, 'label of the eval grid'),
                                    'gridid' => new external_value(PARAM_INT, 'id of the eval grid'),
                                    'sort' => new external_value(PARAM_TEXT, 'label of the eval grid')
                                )
                            )
                        )
                    )
                )
            );
    }

    /**
     * Return the current role for the user
     */
    public static function get_criteria() {
        global $DB;
        $params = self::validate_parameters(self::get_criteria_parameters(), array());
        $criteriadb = $DB->get_records_sql(
            "SELECT crit.id,crit.label,crit.sort as critsort, crit.parentid, ceg.sort as gridsort, ceg.evalgridid as gridid
                    FROM {local_cveteval_criteria} crit
                    LEFT JOIN {local_cveteval_cevalgrid} ceg ON ceg.criteriaid = crit.id
                    ORDER BY ceg.evalgridid, gridsort, crit.parentid, crit.sort ASC
                    "
        );
        $criteria = [];
        foreach ($criteriadb as $cg) {
            if (empty($cg->parentid)) {
                $criteria[$cg->id] = (object) [
                    'id' => (int) $cg->id,
                    'label' => $cg->label,
                    'sort' => (int) $cg->critsort,
                    'gridid' => (int) $cg->gridid,
                    'subcriteria' => []
                ];
            } else {
                if (!empty($criteria[$cg->parentid])) {
                    $criteria[$cg->parentid]->subcriteria[$cg->id] = (object) [
                        'id' => (int) $cg->id,
                        'label' => $cg->label,
                        'sort' => (int) $cg->critsort,
                        'gridid' => (int) $cg->gridid
                    ];
                }
            }
        }
        return array_values($criteria);
    }


}
