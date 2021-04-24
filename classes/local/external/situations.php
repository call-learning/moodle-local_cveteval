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
use user_picture;

class situations extends \external_api {


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_situations_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'id of the user', null, NULL_NOT_ALLOWED)
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function get_user_sheduled_situations_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'the clinical situation id'),
                    'title' => new external_value(PARAM_TEXT, 'the clinical situation title'),
                    'description' => new external_value(PARAM_RAW, 'the clinical situation description'),
                    'starttime' => new external_value(PARAM_INT, 'the clinical situation start time'),
                    'endtime' => new external_value(PARAM_INT, 'the clinical situation end time'),
                    'type' => new external_value(PARAM_TEXT, 'the clinical situation type (appraiser or student)'),
                    'studentname' => new external_value(PARAM_TEXT, 'student name if this is an appraisal (see type)',
                        VALUE_OPTIONAL),
                    'studentpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                        VALUE_OPTIONAL),
                    'studentid' => new external_value(PARAM_TEXT, 'student id if this is an appraisal (see type)',
                        VALUE_OPTIONAL),
                    'appraisalsrequired' => new external_value(PARAM_INT, 'the number of evaluation needed')
                )
            )
        );
    }

    /**
     * Return the current role for the user
     */
    public static function get_user_sheduled_situations($userid) {
        global $DB;
        $params = self::validate_parameters(self::get_user_sheduled_situations_parameters(), array('userid' => $userid));
        self::validate_context(\context_system::instance());
        // First all situation as student.
        $sql = "SELECT
            cls.id,
            cls.title,
            cls.description,
            cls.descriptionformat,
            pl.starttime,
            pl.endtime,
            cls.expectedevalsnb
            FROM {local_cveteval_group_assign} uga
            LEFT JOIN {local_cveteval_evalplan} pl ON uga.groupid = pl.groupid
            LEFT JOIN {local_cveteval_clsituation} cls ON cls.id  = pl.clsituationid
            WHERE uga.studentid = :userid AND cls.id IS NOT NULL";

        $studentsituationsdb = $DB->get_records_sql($sql, array('userid' => $userid));

        $studentsituations = array_map(
            function($situationdb) {
                $situationdb->description = format_text($situationdb->description, $situationdb->descriptionformat);
                $situationdb->type = situation_entity::SITUATION_TYPE_STUDENT;
                $situationdb->appraisalsrequired = $situationdb->expectedevalsnb;
                return $situationdb;
            },
            $studentsituationsdb
        );
        // Then all situation for the same user but as appraiser.
        $sql = "SELECT
            cls.id,
            cls.title,
            cls.description,
            cls.descriptionformat,
            pl.starttime,
            pl.endtime,
            cls.expectedevalsnb,
            uga.studentid
            FROM {local_cveteval_role} sr
            LEFT JOIN {local_cveteval_clsituation} cls ON sr.clsituationid = cls.id
            LEFT JOIN {local_cveteval_evalplan} pl ON cls.id = pl.clsituationid
            LEFT JOIN {local_cveteval_group_assign} uga ON uga.groupid = pl.groupid
            WHERE sr.userid = :userid AND cls.id IS NOT NULL";

        // Check for appraiser : all students that are on this appraiser's situation.
        $appraisersituationsdb = $DB->get_records_sql($sql, array('userid' => $userid));

        $appraisersituations = array_map(
            function($situationdb) {
                global $PAGE;
                $user = \core_user::get_user($situationdb->studentid);
                $userpicture = new user_picture($user);
                $userpicture->includetoken = true;
                $userpicture->size = 1; // Size f1.
                $situationdb->studentname = fullname($user);

                $situationdb->studentpictureurl = $userpicture->get_url($PAGE)->out(false);
                $situationdb->description = format_text($situationdb->description, $situationdb->descriptionformat);
                $situationdb->type = situation_entity::SITUATION_TYPE_APPRAISER;
                $situationdb->appraisalsrequired = $situationdb->expectedevalsnb;
                return $situationdb;
            },
            $appraisersituationsdb
        );

        return $studentsituations + $appraisersituations;
    }




    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_scheduled_situations_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'id of the user', null, NULL_NOT_ALLOWED)
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function get_user_sheduled_situations_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'the clinical situation id'),
                    'title' => new external_value(PARAM_TEXT, 'the clinical situation title'),
                    'description' => new external_value(PARAM_RAW, 'the clinical situation description'),
                    'starttime' => new external_value(PARAM_INT, 'the clinical situation start time'),
                    'endtime' => new external_value(PARAM_INT, 'the clinical situation end time'),
                    'type' => new external_value(PARAM_TEXT, 'the clinical situation type (appraiser or student)'),
                    'studentname' => new external_value(PARAM_TEXT, 'student name if this is an appraisal (see type)',
                        VALUE_OPTIONAL),
                    'studentpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                        VALUE_OPTIONAL),
                    'studentid' => new external_value(PARAM_TEXT, 'student id if this is an appraisal (see type)',
                        VALUE_OPTIONAL),
                    'appraisalsrequired' => new external_value(PARAM_INT, 'the number of evaluation needed')
                )
            )
        );
    }

    /**
     * Return the current role for the user
     */
    public static function get_user_sheduled_situations($userid) {
        global $DB;
        $params = self::validate_parameters(self::get_user_sheduled_situations_parameters(), array('userid' => $userid));
        self::validate_context(\context_system::instance());
        // First all situation as student.
        $sql = "SELECT
            cls.id,
            cls.title,
            cls.description,
            cls.descriptionformat,
            pl.starttime,
            pl.endtime,
            cls.expectedevalsnb
            FROM {local_cveteval_group_assign} uga
            LEFT JOIN {local_cveteval_evalplan} pl ON uga.groupid = pl.groupid
            LEFT JOIN {local_cveteval_clsituation} cls ON cls.id  = pl.clsituationid
            WHERE uga.studentid = :userid AND cls.id IS NOT NULL";

        $studentsituationsdb = $DB->get_records_sql($sql, array('userid' => $userid));

        $studentsituations = array_map(
            function($situationdb) {
                $situationdb->description = format_text($situationdb->description, $situationdb->descriptionformat);
                $situationdb->type = situation_entity::SITUATION_TYPE_STUDENT;
                $situationdb->appraisalsrequired = $situationdb->expectedevalsnb;
                return $situationdb;
            },
            $studentsituationsdb
        );
        // Then all situation for the same user but as appraiser.
        $sql = "SELECT
            cls.id,
            cls.title,
            cls.description,
            cls.descriptionformat,
            pl.starttime,
            pl.endtime,
            cls.expectedevalsnb,
            uga.studentid
            FROM {local_cveteval_role} sr
            LEFT JOIN {local_cveteval_clsituation} cls ON sr.clsituationid = cls.id
            LEFT JOIN {local_cveteval_evalplan} pl ON cls.id = pl.clsituationid
            LEFT JOIN {local_cveteval_group_assign} uga ON uga.groupid = pl.groupid
            WHERE sr.userid = :userid AND cls.id IS NOT NULL";

        // Check for appraiser : all students that are on this appraiser's situation.
        $appraisersituationsdb = $DB->get_records_sql($sql, array('userid' => $userid));

        $appraisersituations = array_map(
            function($situationdb) {
                global $PAGE;
                $user = \core_user::get_user($situationdb->studentid);
                $userpicture = new user_picture($user);
                $userpicture->includetoken = true;
                $userpicture->size = 1; // Size f1.
                $situationdb->studentname = fullname($user);

                $situationdb->studentpictureurl = $userpicture->get_url($PAGE)->out(false);
                $situationdb->description = format_text($situationdb->description, $situationdb->descriptionformat);
                $situationdb->type = situation_entity::SITUATION_TYPE_APPRAISER;
                $situationdb->appraisalsrequired = $situationdb->expectedevalsnb;
                return $situationdb;
            },
            $appraisersituationsdb
        );

        return $studentsituations + $appraisersituations;
    }
}
