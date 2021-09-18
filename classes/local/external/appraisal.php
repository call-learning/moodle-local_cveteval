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
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use local_cveteval\local\persistent\appraisal\entity;

/**
 * Class appraisal
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appraisal extends base_get_entity {

    /**
     * Return the elements
     *
     * @return external_multiple_structure
     */
    public static function get_returns() {
        return new external_multiple_structure(
            static::single_appraisal_returns()
        );
    }

    protected static function single_appraisal_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'id of the appraisal criterion'),
                'studentid' => new external_value(PARAM_INT, 'id of the student'),
                'appraiserid' => new external_value(PARAM_INT, 'id of the appraiser'),
                'evalplanid' => new external_value(PARAM_INT, 'id of the evalplan'),
                'context' => new external_value(PARAM_TEXT, 'context'),
                'contextformat' => new external_value(PARAM_INT, 'context format', VALUE_DEFAULT, FORMAT_PLAIN),
                'comment' => new external_value(PARAM_TEXT, 'comment'),
                'commentformat' => new external_value(PARAM_INT, 'comment format', VALUE_DEFAULT, FORMAT_PLAIN),
                'timemodified' => new external_value(PARAM_INT, 'last modification time'),
                'timecreated' => new external_value(PARAM_INT, 'last modification time'),
                'usermodified' => new external_value(PARAM_INT, 'user modified'),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function submit_returns() {
        return static::single_appraisal_returns();
    }

    /**
     * Return the elements
     */
    public static function submit($id, $studentid, $appraiserid, $evalplanid, $context, $contextformat, $comment, $commentformat,
        $timemodified, $timecreated, $usermodified) {
        // TODO: leverage the persistent entities features to get the right columns/fields to return.
        $params = self::validate_parameters(self::submit_parameters(),
            compact('id', 'studentid', 'appraiserid', 'evalplanid', 'context', 'contextformat', 'comment',
                'commentformat', 'timemodified', 'timecreated', 'usermodified'));
        $context = context_system::instance();
        self::validate_context($context);

        $appraisal = null;
        $entities = self::entities_submit([$params], entity::class);
        if (!empty($entities)) {
            return $entities[0];
        }
        return $appraisal;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function submit_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'id of the appraisal criterion', VALUE_DEFAULT),
                'studentid' => new external_value(PARAM_INT, 'id of the student'),
                'appraiserid' => new external_value(PARAM_INT, 'id of the appraiser'),
                'evalplanid' => new external_value(PARAM_INT, 'id of the evalplan'),
                'context' => new external_value(PARAM_TEXT, 'context', VALUE_DEFAULT, ""),
                'contextformat' => new external_value(PARAM_INT, 'context format', VALUE_DEFAULT, FORMAT_PLAIN),
                'comment' => new external_value(PARAM_TEXT, 'comment', VALUE_DEFAULT, ""),
                'commentformat' => new external_value(PARAM_INT, 'comment format', VALUE_DEFAULT, FORMAT_PLAIN),
                'timemodified' => new external_value(PARAM_INT, 'last modification time', VALUE_DEFAULT, 0),
                'timecreated' => new external_value(PARAM_INT, 'last modification time', VALUE_DEFAULT, 0),
                'usermodified' => new external_value(PARAM_INT, 'user modified', VALUE_DEFAULT, 0),
            )
        );
    }

}

