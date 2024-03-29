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

use context;
use local_cltools\local\field\date;
use local_cltools\local\field\datetime;
use local_cltools\local\field\editor;
use local_cltools\local\field\hidden;
use local_cltools\local\field\number;
use local_cltools\local\field\text;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\roles;
use moodle_url;

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
     * @param string|null $uniqueid a random unique id
     * @param array|null $actionsdefs an array of action
     * @param bool $editable is the table editable ?
     *
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct(?string $uniqueid,
            ?array $actionsdefs,
            bool $editable = false) {
        global $PAGE;
        $this->fieldaliases = [
                'studentid' => 'groupa.studentid',
                'studentfullname' => 'student.fullname',
                'assessorfullname' => 'assessor.fullname',
        ];
        parent::__construct($uniqueid, $actionsdefs, $editable);
        $PAGE->requires->js_call_amd('local_cltools/tabulator-row-action-url', 'init', [
                $this->get_unique_id(),
                (new moodle_url('/local/cveteval/pages/assessment/assess.php'))->out(),
                (object) array('evalplanid' => 'planid', 'studentid' => 'studentid')
        ]);
    }

    /**
     * Default property definition
     *
     * Add all the fields from persistent class except the reserved ones
     *
     */
    protected function setup_fields() {
        $this->fields = [
            new hidden(['fieldname' => 'id', 'rawtype' => PARAM_INT]),
            new hidden(['fieldname' => 'planid', 'rawtype' => PARAM_INT]),
            new hidden(['fieldname' => 'studentid', 'rawtype' => PARAM_INT]),
            new hidden(['fieldname' => 'assessorid', 'rawtype' => PARAM_INT]),
            new hidden(['fieldname' => 'situationid', 'rawtype' => PARAM_INT]),
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

    /**
     * Comment column
     *
     * @param object $row
     * @return string
     */
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
        $fields[] = $DB->sql_concat('plan.id', 'groupa.studentid') . " AS id";
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

    /**
     * Get SQL component parts
     *
     * @param string $tablealias
     * @return string
     */
    protected function internal_get_sql_from($tablealias = 'e') {
        global $DB;
        $groupasql = group_assignment_entity::get_historical_sql_query("groupa");
        return $groupasql .
            ' LEFT JOIN {local_cveteval_evalplan} plan ON plan.groupid = groupa.groupid
         LEFT JOIN {local_cveteval_clsituation} situation ON plan.clsituationid = situation.id
         LEFT JOIN {local_cveteval_finalevl} eval ON eval.evalplanid = plan.id AND groupa.studentid = eval.studentid
         LEFT JOIN (SELECT ' . $DB->sql_concat('u.firstname', 'u.lastname') . ' AS fullname, u.id FROM {user} u ) assessor
            ON assessor.id = eval.assessorid
         LEFT JOIN (SELECT ' . $DB->sql_concat('u.firstname', 'u.lastname') . ' AS fullname, u.id FROM {user} u ) student
            ON student.id = groupa.studentid
        ';
    }

    /**
     * Validate current user has access to the table instance
     *
     * Note: this can involve a more complicated check if needed and requires filters and all
     * setup to be done in order to make sure we validated against the right information
     * (such as for example a filter needs to be set in order not to return data a user should not see).
     *
     * @param context $context
     * @param bool $writeaccess
     */
    public static function validate_access(context $context, bool $writeaccess = false): bool {
        global $USER;
        return roles::can_appraise($USER->id);
    }
}
