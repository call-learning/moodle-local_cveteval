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

use context;
use core_table\local\filter\filter;
use local_cltools\local\field\boolean;
use local_cltools\local\field\date;
use local_cltools\local\field\hidden;
use local_cltools\local\field\text;
use local_cltools\local\filter\enhanced_filterset;
use local_cltools\local\filter\numeric_comparison_filter;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\roles;
use moodle_url;

/**
 * A list of student matching this situation
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mystudents extends dynamic_table_sql {

    /**
     * Constructor for dynamic table
     *
     * @param string|null $uniqueid a random unique id
     * @param array|null $actionsdefs an array of action
     * @param bool $editable is the table editable ?
     * @param int|null $situationid situation identifier
     */
    public function __construct(?string $uniqueid,
            ?array $actionsdefs,
            bool $editable = false,
            ?int $situationid = null) {
        global $PAGE;
        $filterset = new enhanced_filterset([
                'situationid' => (object)
                [
                        'filterclass' => numeric_comparison_filter::class,
                        'required' => true
                ],
        ]);
        if ($situationid) {
            // Either given by value in the constructor or passed by parameter later in the dynamic table.
            $filterset->add_filter_from_params(
                    'situationid', // Field name.
                    filter::JOINTYPE_ALL,
                    [['direction' => '=', 'value' => $situationid]]
            );
        }
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $this->filterset = $filterset;
        $this->fieldaliases = [
                'roletype' => 'role.type',
                'appraiserid' => 'role.userid',
                'situationid' => 'situation.id',
                'appraisalcount' => 'appraisalcount',
                'appraisalrequired' => 'situation.expectedevalsnb',
                'studentfullname' => 'student.fullname',
                'groupname' => 'grp.name',
                'hasgrade' => '(eval.id IS NOT NULL)'
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
                new hidden(['fieldname' => 'planid', 'rawtype' => PARAM_INT]),
                new hidden(['fieldname' => 'studentid', 'rawtype' => PARAM_INT]),
                new hidden(['fieldname' => 'situationid', 'rawtype' => PARAM_INT]),
                new text(['fieldname' => 'studentfullname', 'fullname' => get_string("appraisal:student", 'local_cveteval')]),
                new text(['fieldname' => 'groupname', 'fullname' => get_string("planning:groupname", 'local_cveteval')]),
                new date(['fieldname' => 'starttime', 'fullname' => get_string("planning:starttime", 'local_cveteval')]),
                new date(['fieldname' => 'endtime', 'fullname' => get_string("planning:endtime", 'local_cveteval')]),
                new text(['fieldname' => 'appraisalcount', 'fullname' => get_string("appraisal:count", 'local_cveteval')]),
                new text(['fieldname' => 'appraisalrequired',
                        'fullname' => get_string("planning:requiredappraisals", 'local_cveteval')]),
                new boolean(['fieldname' => 'hasgrade', 'fullname' => get_string("evaluation:hasgrade", 'local_cveteval')]),
        ];
        $this->setup_other_fields();
    }

    /**
     * Get SQL from
     * @param string $tablealias
     * @return string
     */
    protected function internal_get_sql_from($tablealias = 'e') {
        global $DB;
        $planningsql = planning_entity::get_historical_sql_query("plan");
        // As we limit per situation and planning and this situation belongs to the right history.
        return "$planningsql
        LEFT JOIN {" . situation_entity::TABLE . "} situation ON situation.id =  plan.clsituationid
        LEFT JOIN {" . group_assignment_entity::TABLE . "} groupa ON groupa.groupid = plan.groupid
        LEFT JOIN {" . group_entity::TABLE . "} grp ON groupa.groupid = grp.id
        LEFT JOIN {local_cveteval_role} role ON plan.clsituationid = role.clsituationid
        LEFT JOIN (SELECT " . $DB->sql_fullname('s.firstname', 's.lastname') . " AS fullname, s.id FROM {user} s ) student
        ON student.id = groupa.studentid
        LEFT JOIN (SELECT a.studentid AS studentid, a.evalplanid AS planid, COUNT(*) AS count
            FROM {local_cveteval_appraisal} a GROUP BY a.studentid, a.evalplanid) apc
            ON apc.studentid = student.id AND apc.planid = plan.id
        LEFT JOIN {local_cveteval_finalevl} eval ON eval.studentid = student.id AND eval.evalplanid = plan.id";
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
        $fields = [];
        $fields[] = $DB->sql_concat('plan.id', 'student.id') . ' AS id';
        $fields[] = 'plan.id AS planid';
        $fields[] = 'student.id AS studentid';
        $fields[] = 'situation.id AS situationid';
        $fields[] = 'plan.starttime AS starttime';
        $fields[] = 'plan.endtime AS endtime';
        $fields[] = 'grp.name AS groupname';
        $fields[] = 'student.fullname AS studentfullname';
        $fields[] = 'COALESCE(apc.count,0) AS appraisalcount';
        $fields[] = 'situation.expectedevalsnb as appraisalrequired';
        $fields[] = '(eval.id IS NOT NULL) as hasgrade';
        return "DISTINCT " . join(', ', $fields);
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

