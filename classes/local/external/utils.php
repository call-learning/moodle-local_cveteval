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

use dml_exception;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use stdClass;

class utils {

    /**
     * Additional query to retrieve values from a simple entity (planning, criteria, situation)
     *
     * @param $entitytype
     * @param $contextid
     * @return array
     */
    protected static function get_entity_additional_query($entitytype) {
        global $USER;
        $paramscheckrole = ['rolecheckstudentid' => $USER->id, 'rolecheckappraiserid' => $USER->id,'rolechecktypeappraiser' => role_entity::ROLE_APPRAISER_ID,
                            'rolechecktypeassessor' => role_entity::ROLE_ASSESSOR_ID];
        switch ($entitytype) {
            // Here we make sure that current user can only see evalplan involving him/her.
            case 'planning':
                return
                    [
                        '(ga.studentid = :rolecheckstudentid OR ( role.userid = :rolecheckappraiserid AND (role.type = :rolechecktypeappraiser OR
               role.type = :rolechecktypeassessor )))',
                        $paramscheckrole,
                        'LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = e.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = e.clsituationid',
                        'ORDER BY e.starttime ASC',
                        []
                    ];
            case 'appraisal':
                return
                    [
                        '(e.studentid = :rolecheckstudentid OR ( role.userid = :rolecheckappraiserid AND (role.type = :rolechecktypeappraiser OR
               role.type = :rolechecktypeassessor )))',
                        $paramscheckrole,
                        'LEFT JOIN {local_cveteval_evalplan} eplan ON eplan.id = e.evalplanid
                        LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = eplan.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = eplan.clsituationid',
                        'ORDER BY e.timemodified ASC',
                        []
                    ];
            case 'appraisal_criterion':
                return
                    [
                        '(appr.studentid = :rolecheckstudentid OR ( role.userid = :rolecheckappraiserid AND (role.type = :rolechecktypeappraiser OR
               role.type = :rolechecktypeassessor )))',
                        $paramscheckrole,
                        'LEFT JOIN {local_cveteval_appraisal} appr ON appr.id = e.appraisalid
                        LEFT JOIN {local_cveteval_evalplan} eplan ON eplan.id = appr.evalplanid
                        LEFT JOIN {local_cveteval_group_assign} ga ON ga.groupid = eplan.groupid
                        LEFT JOIN {local_cveteval_role} role ON role.clsituationid = eplan.clsituationid',
                        'ORDER BY e.appraisalid, e.timemodified ASC',
                        []
                    ];
            default:
                return ['1=1', [], '', '', '',[]];
        }
    }

    /**
     * @param $entitytype
     * @param string $queryjson
     * @param array $query associative array (a column => value)
     * @return false|int|mixed
     * @throws \dml_exception
     */
    public static function query_entities($entitytype, $query, $select=null) {
        global $DB;
        $classname = '\\local_cveteval\\local\\persistent\\' . $entitytype. '\\entity';
        if (!class_exists($classname)) {
            throw new dml_exception('entitydoesnotexist', $classname);
        }
        // As query can be an arbitrary string we need to to make sure we check that the field
        // names are the right ones (and there is no SQL injection)
        // The rest is handled by $DB->fix_sql_params($sql, $params);
        $columns = $DB->get_columns($classname::TABLE);
        if (is_object($query)) {
            $query = (array) $query;
        }
        list($where, $params, $additionaljoin, $orderby, $additionalcolumns) = static::get_entity_additional_query($entitytype);
        foreach ($query as $key=>$value) {
            if (in_array($key, $additionalcolumns)) {
                continue; // We have a key we need for this query here.
            }
            if (!isset($columns[$key])) {
                $a = new stdClass();
                $a->fieldname = $key;
                $a->tablename = $classname::TABLE;
                throw new dml_exception('ddlfieldnotexist', $a);
            }
            $column = $columns[$key];
            if ($column->meta_type == 'X') {
                //ok so the column is a text column. sorry no text columns in the where clause conditions
                throw new dml_exception('textconditionsnotallowed', $query);
            }
            $params[$key] = $value;
            $where .= empty($where) ? '': ' AND';
            $where .= " e.$key =:$key";
        }
        if (empty($select)) {
            $select = $classname::get_sql_fields('e','');
        }
        if (class_exists($classname)) {
            return $DB->get_records_sql("SELECT DISTINCT $select
                    FROM {" . $classname::TABLE . "} e $additionaljoin WHERE $where $orderby", $params);
        }
        return false;
    }
}
