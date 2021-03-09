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
 * Planning list for a given user
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\assessment;
defined('MOODLE_INTERNAL') || die();

use local_cltools\local\field\base;
use local_cltools\local\filter\basic_filterset;
use local_cltools\local\filter\filter;
use local_cltools\local\table\dynamic_table_sql;
use moodle_url;
use local_cveteval\local\persistent\role\entity as role_entity;

/**
 * A list of student matching this situation
 *
 * @package   local_cltools
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mystudents extends dynamic_table_sql {


    public function __construct($uniqueid) {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_cveteval/row-click-jumpurl', 'init', [
            $uniqueid,
            (new moodle_url('/local/cveteval/pages/assessment/assess.php'))->out(),
            (object) array('evalplanid' => 'planid', 'studentid' => 'studentid')
        ]);
        parent::__construct($uniqueid);
        $this->filteraliases = [
            'roletype' => 'role.type',
            'appraiserid' => 'role.userid',
            'situationid' => 'situation.id',
            'appraisalcount' => 'apc.count',
            'appraisalrequired' => 'situation.expectedevalsnb',
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
                "fullname" => 'planstudentid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'planid' => [
                "fullname" => 'planid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'studentid' => [
                "fullname" => 'studentid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'situationid' => [
                "fullname" => 'situationid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"

            ],
            'studentfullname' => [
                "fullname" => get_string("appraisal:student", 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
            ],
            'groupname' => [
                "fullname" => get_string("planning:groupname", 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
            ],
            'starttime' => [
                "fullname" => get_string("planning:starttime", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "date"
            ],
            'endtime' => [
                "fullname" => get_string("planning:endtime", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "date"
            ],
            'appraisalcount' => [
                "fullname" => get_string("appraisal:count", 'local_cveteval'),
                "rawtype" => PARAM_INT,
            ],
            'appraisalrequired' => [
                "fullname" => get_string("planning:requiredappraisals", 'local_cveteval'),
                "rawtype" => PARAM_INT,
            ],
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
    protected function set_entity_sql() {
        global $USER, $DB;

        $from = ' 
        {local_cveteval_evalplan} plan
        LEFT JOIN {local_cveteval_role} role ON plan.clsituationid = role.clsituationid
        LEFT JOIN {local_cveteval_group_assign} groupa ON groupa.groupid = plan.groupid
        LEFT JOIN {local_cveteval_group} grp ON groupa.groupid = grp.id  
        LEFT JOIN {local_cveteval_clsituation} situation ON situation.id =  plan.clsituationid
        LEFT JOIN (SELECT '.$DB->sql_fullname('s.firstname','s.lastname').' AS fullname, s.id FROM {user} s ) student
        ON student.id = groupa.studentid 
        LEFT JOIN (SELECT a.studentid AS studentid, a.appraiserid AS appraiserid, COUNT(*) AS count
            FROM {local_cveteval_appraisal} a GROUP BY a.studentid, a.appraiserid) apc 
            ON apc.appraiserid = role.userid AND apc.studentid = student.id 
        ';
        $fields[] = $DB->sql_concat('plan.id','student.id') . ' AS id';
        $fields[] = 'plan.id AS planid';
        $fields[] = 'student.id AS studentid';
        $fields[] = 'situation.id AS situationid';
        $fields[] = 'plan.starttime AS starttime';
        $fields[] = 'plan.endtime AS endtime';
        $fields[] = 'grp.name AS groupname';
        $fields[] = 'student.fullname AS studentfullname';
        $fields[] = 'COALESCE(apc.count,0) AS appraisalcount';
        $fields[] = 'situation.expectedevalsnb as appraisalrequired';
        $this->set_sql(join(', ', $fields), $from, '');
    }
}
