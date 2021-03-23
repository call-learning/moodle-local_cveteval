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
 * Situations list for a given student with grades
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\assessment;
defined('MOODLE_INTERNAL') || die();

use local_cltools\local\dmlutils;
use local_cltools\local\field\base;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\local\persistent\role\entity as role_entity;

/**
 * A list of student matching this situation
 *
 * @package   local_cltools
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class situations_student extends dynamic_table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->fieldaliases = [
            'studentid' => 'groupa.studentid',
            'studentfullname' => 'student.fullname',
            'assessorfullname' => 'assessor.fullname',
        ];
    }

    /**
     * Default property definition
     *
     * Add all the fields from persistent class except the reserved ones
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function setup_fields() {
        $fields = [
            'id' => [
                "fullname" => 'situationid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'studentid' => [
                "fullname" => 'studentid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'assessorid' => [
                "fullname" => 'assessorid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'situationid' => [
                "fullname" => 'situationid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'situationtitle' => [
                "fullname" => get_string("situation:title", 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
                "type" => "text"
            ],
            'assessorfullname' => [
                "fullname" => get_string("evaluation:assessor", 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
                "type" => "text"
            ],
            'grade' => [
                "fullname" => get_string("evaluation:grade", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "number"
            ],
            'comment' => [
                "fullname" => get_string("evaluation:comment", 'local_cveteval'),
                "rawtype" => PARAM_RAW,
                "type" => "text"
            ],
            'commentformat' => [
                "fullname" => "commentformat",
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'evaluationdate' => [
                "fullname" => get_string("evaluation:date", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "datetime"
            ],
            'startdate' => [
                "fullname" => get_string("planning:starttime", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "datetime"
            ],
            'enddate' => [
                "fullname" => get_string("planning:starttime", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "datetime"
            ]
        ];
        $this->fields = [];
        foreach ($fields as $name => $prop) {
            $this->fields[$name] = base::get_instance_from_def($name, $prop);
        }
        $this->setup_other_fields();
    }

    /**
     * Set SQL parameters (where, from,....) from the entity
     *
     * This can be overridden when we are looking at linked entities.
     */
    protected function set_initial_sql() {
        global $DB;
        $from = ' 
         {local_cveteval_group_assign} groupa 
         LEFT JOIN {local_cveteval_evalplan} plan ON plan.groupid = groupa.groupid
         LEFT JOIN {local_cveteval_clsituation} situation ON plan.clsituationid = situation.id
         LEFT JOIN {local_cveteval_finalevl} eval ON eval.evalplanid = plan.id 
         LEFT JOIN (SELECT '.$DB->sql_concat('u.firstname', 'u.lastname') .' AS fullname, u.id FROM mdl_user u ) assessor
            ON assessor.id = eval.assessorid
         LEFT JOIN (SELECT '.$DB->sql_concat('u.firstname', 'u.lastname') .' AS fullname, u.id FROM mdl_user u ) student
            ON student.id = groupa.studentid
        ';
        $fields[] = 'situation.id AS id';
        $fields[] = 'groupa.studentid AS studentid';
        $fields[] = 'eval.assessorid AS assessorid';
        $fields[] = 'situation.id AS situationid';
        $fields[] = 'situation.title AS situationtitle';
        $fields[] = 'assessor.fullname AS assessorfullname';
        $fields[] = 'eval.grade AS grade';
        $fields[] = 'eval.comment AS comment';
        $fields[] = 'eval.commentformat AS commentformat';
        $fields[] = 'eval.timemodified AS evaluationdate';
        $fields[] = 'plan.starttime AS startdate';
        $fields[] = 'plan.endtime AS enddate';
        $this->set_sql(join(', ', $fields), $from,'1=1', []);
    }

    protected function col_comment($row) {
        return $this->format_text($row->comment, $row->commentformat);
    }
}

