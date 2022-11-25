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

use core_user\output\myprofile\renderer;
use dml_exception;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use moodle_exception;
use moodle_url;
use stdClass;

/**
 * Class utils
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_utils {

    /**
     * Query entities
     *
     * @param string $entitytype
     * @param array $query associative array (a column => value)
     * @param string|null $select
     * @param int|null $asuser
     * @return array|false
     * @throws \coding_exception
     * @throws dml_exception
     */
    public static function query_entities($entitytype, $query, $select = null, $asuser = null) {
        global $DB;
        $classname = '\\local_cveteval\\local\\persistent\\' . $entitytype . '\\entity';
        if (!class_exists($classname)) {
            throw new dml_exception('entitydoesnotexist', $classname);
        }
        // As query can be an arbitrary string we need to to make sure we check that the field
        // names are the right ones (and there is no SQL injection)
        // The rest is handled by $DB->fix_sql_params($sql, $params).
        $columns = $DB->get_columns($classname::TABLE);
        if (is_object($query)) {
            $query = (array) $query;
        }
        list($where, $params, $additionaljoin, $orderby, $additionalfields)
            = static::get_entity_additional_query($entitytype, $asuser);
        foreach ($query as $key => $value) {
            if (!isset($columns[$key])) {
                $a = new stdClass();
                $a->fieldname = $key;
                $a->tablename = $classname::TABLE;
                throw new dml_exception('ddlfieldnotexist', $a);
            }
            $column = $columns[$key];
            if ($column->meta_type == 'X') {
                // Ok so the column is a text column. sorry no text columns in the where clause conditions.
                throw new dml_exception('textconditionsnotallowed', $query);
            }
            $where .= empty($where) ? '' : ' AND';
            if (is_object($value)) {
                if (!empty($value->operator) && trim($value->operator) == 'in') {
                    [$sqlwhere, $additionalparams] = $DB->get_in_or_equal($value->values, SQL_PARAMS_NAMED);
                    $params = array_merge($params, $additionalparams);
                    $where .= " e.$key $sqlwhere";
                }
            } else {
                $params[$key] = $value;
                $where .= " e.$key =:$key";
            }
        }
        if (empty($select)) {
            $select = $classname::get_sql_fields('e', '');
            if (!empty($additionalfields)) {
                $select .= "," . join(",", $additionalfields);
            }
        }
        if (class_exists($classname)) {
            $entitytable = "{" . $classname::TABLE . "} AS e";
            if (in_array(model_with_history::class, class_implements($classname))) {
                $entitytable = $classname::get_historical_sql_query_for_id();
            }
            return $DB->get_records_sql("SELECT DISTINCT $select
                    FROM $entitytable $additionaljoin WHERE $where $orderby", $params);
        }
        return false;
    }

    /**
     * Additional query to retrieve values from a simple entity (planning, criteria, situation)
     *
     * @param string $entitytype
     * @param int|null $asuser query as a given user
     * @return array
     */
    protected static function get_entity_additional_query($entitytype, $asuser = null) {
        global $USER;
        if ($asuser) {
            $user = \core_user::get_user($asuser);
        } else {
            $user = $USER;
        }
        $paramscheckrole = ['rolecheckstudentid' => $user->id, 'rolecheckappraiserid' => $user->id,
                'rolechecktypeappraiser' => role_entity::ROLE_APPRAISER_ID,
                'rolechecktypeassessor' => role_entity::ROLE_ASSESSOR_ID];
        $paramscheckroleappraisal = $paramscheckrole;
        $paramscheckroleappraisal['appraisalcheckstudentid'] = $user->id;
        switch ($entitytype) {
            // Here we make sure that current user can only see evalplan involving him/her.
            case 'planning':
                return [
                        '( ga.studentid = :rolecheckstudentid OR ( role.userid = :rolecheckappraiserid AND'
                        . ' (role.type = :rolechecktypeappraiser OR role.type = :rolechecktypeassessor )))',
                        $paramscheckrole,
                        'LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = e.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = e.clsituationid',
                        'ORDER BY e.starttime ASC',
                        []
                ];
            case 'appraisal':
                return [
                        '( eplan.id IS NOT NULL AND (( ga.studentid = :rolecheckstudentid
                        AND e.studentid = :appraisalcheckstudentid )'
                        . ' OR ( role.userid = :rolecheckappraiserid AND (role.type = :rolechecktypeappraiser OR
               role.type = :rolechecktypeassessor ))))',
                        $paramscheckroleappraisal,
                        "LEFT JOIN "
                        . planning_entity::get_historical_sql_query_for_id("eplan")
                        . " ON eplan.id = e.evalplanid
                        LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = eplan.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = eplan.clsituationid",
                        'ORDER BY e.evalplanid, e.timecreated ASC',
                        []
                ];
            case 'appraisal_criterion':
                return [
                        '( eplan.id IS NOT NULL AND (( ga.studentid = :rolecheckstudentid
                         AND appr.studentid = :appraisalcheckstudentid )'
                        . ' OR ( role.userid = :rolecheckappraiserid AND (role.type = :rolechecktypeappraiser OR
               role.type = :rolechecktypeassessor ))))',
                        $paramscheckroleappraisal,
                        'LEFT JOIN {local_cveteval_appraisal} appr ON appr.id = e.appraisalid '
                        . "LEFT JOIN "
                        . planning_entity::get_historical_sql_query_for_id("eplan")
                        . " ON eplan.id = appr.evalplanid "
                        . "LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = eplan.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = eplan.clsituationid",
                        'ORDER BY e.appraisalid, e.timecreated ASC',
                        []
                ];
            case 'criterion':
                return ['1=1', [], '', 'ORDER BY realparent ASC, realsort ASC',
                        [
                            'CASE e.parentid WHEN 0 THEN e.id ELSE e.parentid END AS realparent, ' .
                            'CASE e.parentid WHEN 0 THEN 0 ELSE e.sort END AS realsort'
                        ]];
            default:
                return ['1=1', [], '', '', []];
        }
    }

    /**
     * Launch URL
     *
     * @param array $params
     * @return string
     * @throws moodle_exception
     */
    public static function get_application_launch_url($params) {
        $url = new moodle_url('/', $params);
        return "fr.calllearning.competveteval://" . $url->get_query_string();
    }
}
