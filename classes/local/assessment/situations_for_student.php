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

use core_table\local\filter\filter;
use local_cltools\local\field\date;
use local_cltools\local\field\datetime;
use local_cltools\local\field\editor;
use local_cltools\local\field\hidden;
use local_cltools\local\field\number;
use local_cltools\local\field\text;
use local_cltools\local\filter\enhanced_filterset;
use local_cltools\local\table\dynamic_table_sql;
use ReflectionException;

/**
 * A list of student matching this situation
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class situations_for_student extends dynamic_table_sql {

    /**
     * Sets up the page_table parameters.
     *
     * @throws \coding_exception
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct($uniqueid = null,
        $actionsdefs = null,
        $editable = false,
        $studentid = null) {
        $this->fieldaliases = [
            'studentid' => 'groupa.studentid',
            'studentfullname' => 'student.fullname',
            'assessorfullname' => 'assessor.fullname',
        ];
        parent::__construct($uniqueid, $actionsdefs, $editable);
    }

    /**
     * Default property definition
     *
     * Add all the fields from persistent class except the reserved ones
     *
     * @return array
     * @throws ReflectionException
     */
    protected function setup_fields() {
        $this->fields = [
            new hidden(['fieldname' => 'id', 'rawtype' => PARAM_INT ]),
            new hidden(['fieldname' => 'planid', 'rawtype' => PARAM_INT ]),
            new hidden(['fieldname' => 'studentid', 'rawtype' => PARAM_INT ]),
            new hidden(['fieldname' => 'assessorid', 'rawtype' => PARAM_INT ]),
            new hidden(['fieldname' => 'situationid', 'rawtype' => PARAM_INT ]),
            new text(['fieldname' => 'situationtitle', 'fullname' => get_string("situation:title", 'local_cveteval')]),
            new date(['fieldname' => 'startdate', 'fullname' => get_string("planning:starttime", 'local_cveteval')]),
            new date(['fieldname' => 'enddate', 'fullname' => get_string("planning:endtime", 'local_cveteval')]),
            new text(['fieldname' => 'assessorfullname', 'fullname' => get_string("evaluation:assessor", 'local_cveteval')]),
            new number(['fieldname' => 'grade', 'fullname' => get_string("evaluation:grade", 'local_cveteval')]),
            new editor(['fieldname' => 'comment', 'fullname' => get_string("evaluation:comment", 'local_cveteval')]),
            new datetime(['fieldname' => 'evaluationdate', 'fullname' => get_string("evaluation:date", 'local_cveteval')])
        ];
        $this->setup_other_fields();
    }

    protected function col_comment($row) {
        return $this->format_text($row->comment, $row->commentformat);
    }

    /**
     * Get sql fields
     *
     * Overridable sql query
     *
     * @param string $tablealias
     */
    protected function internal_get_sql_fields($tablealias = 'e') {
        global $DB;
        $fields[] = $DB->sql_concat('plan.id', 'groupa.studentid'). " AS id";
        $fields[] = 'plan.id AS planid';
        $fields[] = 'groupa.studentid AS studentid';
        $fields[] = 'eval.assessorid AS assessorid';
        $fields[] = 'situation.id AS situationid';
        $fields[] = 'situation.title AS situationtitle';
        $fields[] = 'assessor.fullname AS assessorfullname';
        $fields[] = 'eval.grade AS grade';
        $fields[] = 'eval.comment AS comment';
        $fields[] = 'eval.commentformat AS commentformat';
        $fields[] = 'COALESCE(eval.timemodified,0) AS evaluationdate';
        $fields[] = 'plan.starttime AS startdate';
        $fields[] = 'plan.endtime AS enddate';
        return "DISTINCT " . join(',', $fields) . " ";
    }

    protected function internal_get_sql_from($tablealias = 'e') {
        global $DB;
        return '
         {local_cveteval_group_assign} groupa
         LEFT JOIN {local_cveteval_evalplan} plan ON plan.groupid = groupa.groupid
         LEFT JOIN {local_cveteval_clsituation} situation ON plan.clsituationid = situation.id
         LEFT JOIN {local_cveteval_finalevl} eval ON eval.evalplanid = plan.id AND groupa.studentid = eval.studentid
         LEFT JOIN (SELECT ' . $DB->sql_concat('u.firstname', 'u.lastname') . ' AS fullname, u.id FROM {user} u ) assessor
            ON assessor.id = eval.assessorid
         LEFT JOIN (SELECT ' . $DB->sql_concat('u.firstname', 'u.lastname') . ' AS fullname, u.id FROM {user} u ) student
            ON student.id = groupa.studentid
        ';
    }
}

