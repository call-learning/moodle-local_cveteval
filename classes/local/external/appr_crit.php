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

use context_system;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_cveteval\local\persistent\appraisal_criterion\entity;

/**
 * Class appr_crit
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appr_crit extends base_get_entity {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_parameters() {
        return new external_function_parameters(
                array(
                        'appraisalcriteriamodels' =>
                                new external_multiple_structure(
                                        new external_single_structure(
                                                array(
                                                        'id' => new external_value(PARAM_INT, 'id of the appraisal criterion',
                                                                VALUE_DEFAULT),
                                                        'criterionid' => new external_value(PARAM_INT, 'id of the criterion'),
                                                        'appraisalid' => new external_value(PARAM_INT, 'id of the appraisal'),
                                                        'grade' => new external_value(PARAM_INT, 'grade for appraisal',
                                                                VALUE_DEFAULT, 0),
                                                        'comment' => new external_value(PARAM_TEXT, 'comment', VALUE_DEFAULT, ""),
                                                        'commentformat' => new external_value(PARAM_INT, 'comment format',
                                                                VALUE_DEFAULT, FORMAT_PLAIN),
                                                        'timemodified' => new external_value(PARAM_INT, 'last modification time',
                                                                VALUE_DEFAULT, 0),
                                                        'timecreated' => new external_value(PARAM_INT, 'last modification time',
                                                                VALUE_DEFAULT, 0),
                                                        'usermodified' => new external_value(PARAM_INT, 'user modified',
                                                                VALUE_DEFAULT, 0),
                                                )
                                        ), VALUE_DEFAULT
                                )
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function submit_returns() {
        return static::get_returns();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function get_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(PARAM_INT, 'id of the appraisal criterion'),
                                'criterionid' => new external_value(PARAM_INT, 'id of the criterion'),
                                'appraisalid' => new external_value(PARAM_INT, 'id of the appraisal'),
                                'grade' => new external_value(PARAM_INT, 'grade for appraisal'),
                                'comment' => new external_value(PARAM_TEXT, 'comment'),
                                'commentformat' => new external_value(PARAM_INT, 'comment format', VALUE_DEFAULT, FORMAT_PLAIN),
                                'timemodified' => new external_value(PARAM_INT, 'last modification time'),
                                'timecreated' => new external_value(PARAM_INT, 'last modification time'),
                                'usermodified' => new external_value(PARAM_INT, 'user modified'),
                        )
                )
        );
    }

    /**
     * Return the elements
     * @param array $appraisals
     * @return object
     */
    public static function submit($appraisals) {
        // TODO: leverage the persistent entities features to get the right columns/fields to return.
        $context = context_system::instance();
        self::validate_context($context);

        $entities = [];
        if (!empty($appraisals)) {
            $entities = self::entities_submit($appraisals, entity::class);
        }
        return $entities;
    }

}

