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
use Matrix\Exception;
use stdClass;
use user_picture;

class appraisals extends \external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function set_user_appraisal_parameters() {
        return new external_function_parameters(
            array(
                'situationid' => new external_value(PARAM_INT, 'id of the situation', VALUE_REQUIRED),
                'appraiserid' => new external_value(PARAM_INT, 'id of the appraiser', VALUE_REQUIRED),
                'studentid' => new external_value(PARAM_INT, 'id of the student', VALUE_REQUIRED),
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'id of the appraised criteria', VALUE_OPTIONAL, 0),
                            'criterionid' => new external_value(PARAM_INT, 'id of the criterion in the list'),
                            'grade' => new external_value(PARAM_INT, 'grade for the criteria', VALUE_OPTIONAL, 0),
                            'comment' => new external_value(PARAM_TEXT, 'comment for criteria', VALUE_OPTIONAL, ""),
                            'subcriteria' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT, 'id of the appraised criteria', VALUE_OPTIONAL, 0),
                                        'criterionid' => new external_value(PARAM_INT, 'id of the criterion in the list'),
                                        'grade' => new external_value(PARAM_INT, 'grade for the criteria', VALUE_OPTIONAL, 0),
                                        'comment' => new external_value(PARAM_TEXT, 'comment for criteria', VALUE_OPTIONAL, ""),
                                    )
                                ), '', VALUE_OPTIONAL
                            )
                        )
                    )
                ),
                'context' => new external_value(PARAM_TEXT, 'context for appraisal', VALUE_OPTIONAL, ""),
                'comment' => new external_value(PARAM_TEXT, 'comment for appraisal', VALUE_OPTIONAL, ""),
                'id' => new external_value(PARAM_INT, 'id of the appraisal if it already exists', VALUE_OPTIONAL, 0),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function set_user_appraisal_returns() {
        return static::get_appraisal_returns();
    }

    /**
     * Return the current role for the user
     */
    public static function set_user_appraisal($situationid, $appraiserid, $studentid, $criteria, $context = "", $comment = "",
        $id = 0) {
        global $DB;
        $params = self::validate_parameters(self::set_user_appraisal_parameters(), compact("situationid",
            "appraiserid", "studentid", "context", "comment", "criteria", "id"));
        // Normally we should have only one matching situation per appraiserId and student
        $sql = "SELECT pl.id FROM {local_cveteval_evalplan} pl 
                LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = pl.groupid
                WHERE pl.clsituationid =:situationid AND ga.studentid = :studentid";
        $evalplanid = $DB->get_field_sql($sql, array('situationid' => $situationid, 'studentid' => $studentid));
        if ($evalplanid) {
            try {
                $transaction = $DB->start_delegated_transaction();
                $appraisalrecord = (object) [
                    'id' => $id,
                    'studentid' => $studentid,
                    'appraiserid' => $appraiserid,
                    'evalplanid' => $evalplanid,
                    'context' => $context,
                    'contextformat' => FORMAT_PLAIN,
                    'comment' => $context,
                    'commentformat' => FORMAT_PLAIN,
                ];
                $appraisal = new appraisal_entity($id, $appraisalrecord);
                if (!$id) {
                    $appraisal->create();
                }
                $appraisal->save();
                $id = $appraisal->get('id');
                foreach ($criteria as $crit) {
                    $critrecord = (object) [
                        'id' => empty($crit['id']) ? 0 : $crit['id'],
                        'criteriaid' => $crit['criterionid'], // TODO : this should criterionid.
                        'appraisalid' => $id,
                        'grade' => empty($crit['grade']) ? 0: $crit['grade'],
                        'comment' => empty($crit['comment']) ? "":$crit['comment']
                    ];
                    $criterion = app_crit_entity::get_record(array('appraisalid' => $id, 'criteriaid' => $crit['criterionid']));
                    if (!$criterion) {
                        $criterion = new app_crit_entity(0, $critrecord);
                        $criterion->create();
                    } else {
                        $criterion->from_record($critrecord);
                    }
                    $criterion->save();
                    if ($crit['subcriteria']) {
                        foreach ($crit['subcriteria'] as $scrit) {
                            $critrecord = (object) [
                                'id' => empty($scrit['id']) ? 0 : $scrit['id'],
                                'criteriaid' => $scrit['criterionid'], // TODO : this should criterionid.
                                'appraisalid' => $id,
                                'grade' => empty($scrit['grade']) ? 0: $scrit['grade']
                            ];
                            $criterion = app_crit_entity::get_record(array('appraisalid' => $id, 'criteriaid' => $scrit['id']));
                            if (!$criterion) {
                                $criterion = new app_crit_entity(0, $critrecord);
                                $criterion->create();
                            } else {
                                $criterion->from_record($critrecord);
                            }
                            $criterion->save();
                        }
                    }
                }
                $transaction->allow_commit();
                return static::get_appraisal($id);
            } catch (Exception $e) {
                $transaction->dispose();
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_user_appraisals_parameters() {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'id of the user', null, NULL_NOT_ALLOWED),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function get_user_appraisals_returns() {
        return new external_multiple_structure(
            static::get_appraisal_returns()
        );
    }

    /**
     * Return the current role for the user
     */
    public static function get_user_appraisals($userid) {
        global $DB;
        $params = self::validate_parameters(self::get_user_appraisals_parameters(), array('userid' => $userid));

        // Get appraisal done for this user either as a student or as an appraiser

        // First all situation as student
        $sql = "SELECT 
            appraisal.id, 
            appraisal.studentid,
            appraisal.appraiserid,
            appraisal.evalplanid,
            appraisal.context,
            appraisal.contextformat,
            appraisal.comment,
            appraisal.commentformat,
            plan.clsituationid as situationid,
            situation.title as situationtitle,
            plan.starttime, 
            plan.endtime,
            COALESCE(appraisal.timemodified, appraisal.timecreated) as timemodified
            FROM {local_cveteval_appraisal} appraisal 
            LEFT JOIN {local_cveteval_evalplan} plan ON plan.id = appraisal.evalplanid
            LEFT JOIN {local_cveteval_clsituation} situation ON situation.id = plan.clsituationid
            WHERE appraisal.studentid = :studentid OR appraisal.appraiserid = :appraiserid";

        $appraisals = $DB->get_records_sql($sql, array('studentid' => $userid, 'appraiserid' => $userid));

        foreach ($appraisals as &$appr) {
            if (empty($appr->situationid)) {
                unset($appraisals[$appr->id]);
                continue; // Appraisal should always have a related situation id.
            }
            static::set_appraisal_criteria($appr);
        }
        return $appraisals;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function get_appraisal_parameters() {
        return new external_function_parameters(
            array(
                'appraisalid' => new external_value(PARAM_INT, 'id of the appraisal'),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function get_appraisal_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'id of the appraisal if it already exists', VALUE_OPTIONAL, 0),
                'situationid' => new external_value(PARAM_INT, 'id of the situation'),
                'situationtitle' => new external_value(PARAM_TEXT, 'id of the situation'),
                'appraiserid' => new external_value(PARAM_INT, 'id of the appraiser'),
                'type' => new external_value(PARAM_INT, '1=appraiser, 2=evaluator'),
                'appraisername' => new external_value(PARAM_TEXT, 'fullname of the appraiser', VALUE_OPTIONAL, ""),
                'appraiserpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                    VALUE_OPTIONAL),
                'studentid' => new external_value(PARAM_INT, 'id of the student'),
                'studentname' => new external_value(PARAM_TEXT, 'fullname of the appraiser'),
                'studentpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                    VALUE_OPTIONAL),
                'timemodified' => new external_value(PARAM_INT, 'last modification time being creation or modification'),
                'context' => new external_value(PARAM_TEXT, 'context for appraisal', VALUE_OPTIONAL, ""),
                'comment' => new external_value(PARAM_TEXT, 'comment for appraisal', VALUE_OPTIONAL, ""),
                'criteria' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT,
                                'id of the appraisal criteria (see local_cveteval_appr_crit)'),
                            'criterionid' => new external_value(PARAM_INT, 'id of the criterion'),
                            'label' => new external_value(PARAM_TEXT, 'label for the criterion'),
                            'grade' => new external_value(PARAM_INT, 'grade for the criterion'),
                            'comment' => new external_value(PARAM_TEXT, 'comment for criterion', VALUE_OPTIONAL, ""),
                            'timemodified' => new external_value(PARAM_INT,
                                'last modification time being creation or modification'),
                            'subcriteria' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'id' => new external_value(PARAM_INT,
                                            'id of the appraisal criteria (see local_cveteval_appr_crit)'),
                                        'criterionid' => new external_value(PARAM_INT, 'id of the criterion'),
                                        'label' => new external_value(PARAM_TEXT, 'label for the criterion'),
                                        'grade' => new external_value(PARAM_INT, 'grade for the criterion'),
                                        'timemodified' => new external_value(PARAM_INT,
                                            'last modification time being creation or modification'),
                                    )
                                ),
                                '',
                                VALUE_OPTIONAL
                            )
                        )
                    )
                ),
            )
        );
    }

    /**
     * Return the current role for the user
     */
    public static function get_appraisal($appraisalid) {
        global $DB;
        $params = self::validate_parameters(self::get_appraisal_parameters(), array('appraisalid' => $appraisalid));

        // Get appraisal done for this user either as a student or as an appraiser

        // First all situation as student
        $sql = "SELECT 
            appraisal.id, 
            appraisal.studentid,
            appraisal.appraiserid,
            appraisal.evalplanid,
            appraisal.context,
            appraisal.contextformat,
            appraisal.comment,
            appraisal.commentformat,
            plan.clsituationid as situationid,
            situation.title as situationtitle,
            plan.starttime, 
            plan.endtime,
            COALESCE(appraisal.timemodified, appraisal.timecreated) as timemodified
            FROM {local_cveteval_appraisal} appraisal 
            LEFT JOIN {local_cveteval_evalplan} plan ON plan.id = appraisal.evalplanid
            LEFT JOIN {local_cveteval_clsituation} situation ON situation.id = plan.clsituationid
            WHERE appraisal.id = :appraisalid";

        $appraisal = $DB->get_record_sql($sql, array('appraisalid' => $appraisalid));

        static::set_appraisal_criteria($appraisal);

        return $appraisal;
    }

    protected static function set_appraisal_criteria(&$appr) {
        global $DB, $PAGE;
        $studentuser = \core_user::get_user($appr->studentid);
        $appraiseruser = \core_user::get_user($appr->appraiserid);
        $appr->studentname = fullname($studentuser);

        $studentpicture = new user_picture($studentuser);
        $studentpicture->includetoken = true;
        $studentpicture->size = 1; // Size f1
        $appr->studentpictureurl = $studentpicture->get_url($PAGE)->out();

        $appr->appraisername = fullname(\core_user::get_user($appr->appraiserid));

        $appraiserpicture = new user_picture($appraiseruser);
        $appraiserpicture->includetoken = true;
        $appraiserpicture->size = 1; // Size f1
        $appr->appraiserpictureurl = $appraiserpicture->get_url($PAGE)->out();

        $appr->context = format_text($appr->context, $appr->contextformat);
        unset($appr->contextformat);
        $appr->comment = format_text($appr->comment, $appr->commentformat);
        unset($appr->commentformat);
        $type = $DB->get_field('local_cveteval_role', 'type',
            array('clsituationid' => $appr->situationid, 'userid' => $appr->appraiserid)
        );
        $appr->type = $type ? (int) $type : role_entity::ROLE_APPRAISER_ID;
        $allapprcriteria = $DB->get_records_sql(
            "SELECT apc.id, apc.criteriaid, apc.grade, apc.comment, apc.commentformat,
                    crit.id AS critid,
                    crit.parentid AS cparentid,
                    crit.sort AS csort,
                    COALESCE(crit.timemodified, crit.timecreated) as timemodified,
                    crit.label
                    FROM {local_cveteval_appr_crit} apc 
                    LEFT JOIN {local_cveteval_criteria} crit ON crit.id = apc.criteriaid
                    WHERE apc.appraisalid =:appraisalid
                    ORDER BY cparentid, csort ASC
                    ",
            array('appraisalid' => $appr->id)
        );
        $rootcriteria = [];
        foreach ($allapprcriteria as $cr) {
            if (empty($cr->cparentid)) {
                $cr->subcriteria = [];
                $rootcriteria[$cr->critid] = (object) [
                    'id' => (int) $cr->critid,
                    'criterionid' => (int) $cr->crid,
                    'grade' => (int) $cr->grade,
                    'label' => $cr->label,
                    'comment' => format_text($cr->comment, $cr->commentformat),
                    'timemodified' => $cr->timemodified,
                    'subcriteria' => []
                ];
            } else {
                if (!empty($rootcriteria[$cr->cparentid])) {
                    $rootcriteria[$cr->cparentid]->subcriteria[] = (object) [
                        'id' => (int) $cr->critid,
                        'criterionid' => (int) $cr->crid,
                        'grade' => (int) $cr->grade,
                        'label' => $cr->label,
                        'timemodified' => $cr->timemodified
                    ];
                }
            }
        }

        $appr->criteria = array_values($rootcriteria);
    }
}
