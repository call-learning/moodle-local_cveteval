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
class appraisals_student extends dynamic_table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->filteraliases = [
            'roletype' => 'role.type',
            'situationid' => 'plan.clsituationid',
            'studentid' => 'appraisal.studentid',
            'appraiserfullname' => 'appraiser.fullname',
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
                "fullname" => 'appraisalid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'studentid' => [
                "fullname" => 'studentid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'appraiserid' => [
                "fullname" => 'appraiserid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'situationid' => [
                "fullname" => 'situationid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'appraiserfullname' => [
                "fullname" => get_string("appraisal:appraiser", 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
                "type" => "text"
            ],
            'context' => [
                "fullname" => get_string("appraisal:context", 'local_cveteval'),
                "rawtype" => PARAM_RAW,
                "type" => "text"
            ],
            'comment' => [
                "fullname" => get_string("appraisal:comment", 'local_cveteval'),
                "rawtype" => PARAM_INT,
                "type" => "text"
            ],
            'datetime' => [
                "fullname" => get_string("appraisal:modificationdate", 'local_cveteval'),
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
    protected function set_entity_sql() {
        global $DB;
        $from = '
         {local_cveteval_appraisal} appraisal 
         LEFT JOIN {local_cveteval_evalplan} plan ON appraisal.evalplanid = plan.id
         LEFT JOIN {local_cveteval_group_assign} groupa ON groupa.groupid = plan.groupid 
         LEFT JOIN {local_cveteval_role} role ON plan.clsituationid = role.clsituationid
         LEFT JOIN (SELECT '.$DB->sql_concat('u.firstname', 'u.lastname') .' AS fullname, u.id FROM mdl_user u ) appraiser
            ON appraiser.id = appraisal.appraiserid
        ';
        $fields[] = 'appraisal.id AS id';
        $fields[] = 'appraisal.appraiserid AS appraiserid';
        $fields[] = 'appraisal.studentid AS studentid';
        $fields[] = 'plan.clsituationid AS situationid';
        $fields[] = 'appraiser.fullname AS appraiserfullname';
        $fields[] = 'appraisal.comment AS comment';
        $fields[] = 'appraisal.commentformat AS commentformat';
        $fields[] = 'appraisal.context AS context';
        $fields[] = 'appraisal.contextformat AS contextformat';
        $fields[] = 'appraisal.timemodified AS datetime';
        $this->set_sql(join(', ', $fields), $from,'1=1', []);
    }
}
