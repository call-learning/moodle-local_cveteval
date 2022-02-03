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

use coding_exception;
use dml_exception;
use local_cltools\local\field\hidden;
use local_cltools\local\field\html;
use local_cltools\local\field\text;
use local_cltools\local\filter\enhanced_filterset;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\output\grade_widget;
use ReflectionException;
use stdClass;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
/**
 * A list of student matching this situation
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appraisals_student extends dynamic_table_sql {

    protected const FIELDS = [
        'criterion.id AS id',
        'criterion.parentid AS criterionparentid',
        'criterion.label AS criterionname',
        'criterion.sort AS criterionsort'
    ];
    /**
     * @var null
     */
    protected $appraiserlist = null;
    /**
     * @var mixed
     */
    private $evalgridparams;
    /**
     * @var mixed
     */
    private $evalgridwhere;

    /**
     * Sets up the page_table parameters.
     *
     * @throws coding_exception
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct($uniqueid = null,
        $actionsdefs = null,
        $editable = false
    ) {
        $this->fieldaliases = [
            'planid' => 'plan.id',
            'studentid' => 'appraisal.studentid',
            'roletype' => 'role.type',
            'criterionname' => 'criterion.label'
        ];
        $this->filterset = null;
        parent::__construct($uniqueid, $actionsdefs, $editable);
    }

    protected function internal_get_sql_from($tablealias = 'e') {
        return criterion_entity::get_historical_sql_query("criterion");
    }

    /**
     * Get sql fields
     *
     * Overridable sql query
     *
     * @param string $tablealias
     */
    protected function internal_get_sql_fields($tablealias = 'e') {
        $fields = static::FIELDS;
        if ($this->appraiserlist) {
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $fields[] = " '' AS " . $this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid);
            }
        }
        return "DISTINCT " . join(',', $fields) . " ";
    }
    /**
     * Get where
     *
     * @param bool $disablefilters
     * @return array
     */
    protected function internal_get_sql_where($disablefilters = false) {
        if ($this->evalgridwhere && $this->evalgridparams) {
            return [' criterion.evalgridid ' . $this->evalgridwhere, $this->evalgridparams];
        }
        return ['1=1', []];
    }

    /**
     * Main method to create the underlying query (SQL)
     *
     * @param int $pagesize
     */
    public function query_db($pagesize, $disablefilters = false) {
        // Very specific use here: we do not use the same filters for criteria and for the observations filterings.
        dynamic_table_sql::query_db($pagesize, true);
    }

    /**
     * Retrieve data from the database and return a row set
     * This is a complete hack here as we have had to transpose the table from the original
     * design.
     *
     * @return array
     */
    public function get_rows($pagesize) {
       if (empty($this->appraiserlist)) {
            return []; // Nothing if we have no appraisers.
        }
        $rows = parent::get_rows($pagesize);
        $rootcriteria = [];
        foreach ($rows as $rcriteria) {
            if (empty($rcriteria->criterionparentid)) {
                $this->get_appraisal_criteria_grade($rcriteria);
                $rcriteria->_children = [];
                $rootcriteria[$rcriteria->id] = $rcriteria;
            }
        }
        foreach ($rows as $rcriteria) {
            if (!empty($rcriteria->criterionparentid)) {
                $this->get_appraisal_criteria_grade($rcriteria);
                $rootcriteria[$rcriteria->criterionparentid]->_children[] = $rcriteria;
            }
        }
        return array_values($rootcriteria);
    }

    /**
     * This is a special version as we add new columns depending on the filters
     *
     * @param enhanced_filterset $filterset The filterset object to get filters and table parameters from
     */
    public function set_filterset(enhanced_filterset $filterset): void {
        parent::set_filterset($filterset);
        list($cols, $headers) = $this->get_table_columns_definitions();
        $this->define_columns($cols);
        $this->define_headers($headers);
    }

    /**
     * Get appraisal criteria grade
     *
     * @param $row
     * @throws dml_exception
     * @throws coding_exception
     */
    public function get_appraisal_criteria_grade(&$row) {
        global $DB;
        global $PAGE;
        $planningsql = planning_entity::get_historical_sql_query("plan");
        $criterionsql = criterion_entity::get_historical_sql_query("criterion");
        $rolesql = role_entity::get_historical_sql_query("role");
        $renderer = $PAGE->get_renderer('local_cveteval');
        if ($this->appraiserlist) {
            list($additionalwhere, $params) = $this->filterset->get_sql_for_filter('',
                array('criterionname'),
                $this->fieldaliases
            );
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $grade = $DB->get_record_sql(
                    "SELECT c.criterionid AS criterionid, c.grade AS grade,
                        c.comment AS comment, c.commentformat,
                        appraisal.comment AS appraisalcomment, appraisal.commentformat AS appraisalcommentformat,
                        appraisal.context AS appraisalcontext, appraisal.contextformat AS appraisalcontextformat
                    FROM {local_cveteval_appr_crit} c
                    LEFT JOIN {local_cveteval_appraisal} appraisal ON appraisal.id = c.appraisalid
                    LEFT JOIN $planningsql ON plan.id = appraisal.evalplanid
                    LEFT JOIN $criterionsql ON criterion.id = c.criterionid
                    LEFT JOIN $rolesql ON plan.clsituationid = role.clsituationid
                        AND role.userid = appraisal.appraiserid
                    WHERE appraisal.appraiserid = :appraiserid AND c.appraisalid = :appraisalid
                    AND c.criterionid = :criterionid
                    AND plan.id IS NOT NULL AND role.id IS NOT NULL AND criterion.id IS NOT NULL
                    AND $additionalwhere",
                    $params + [
                        'appraiserid' => $appraiserid,
                        'criterionid' => $row->id,
                        'appraisalid' => $appraisalid
                    ]

                );
                $subgradescount = $DB->count_records_sql("
                    SELECT COUNT(c.id)
                    FROM {local_cveteval_appr_crit} c
                    LEFT JOIN $criterionsql ON criterion.id = c.criterionid
                    LEFT JOIN {local_cveteval_appraisal} appraisal ON appraisal.id = c.appraisalid
                    WHERE criterion.parentid = :criterionid AND appraisal.appraiserid = :appraiserid 
                        AND c.appraisalid = :appraisalid AND c.grade <> 0
                    ", [
                    'criterionid' => $row->id,
                    'appraisalid' => $appraisalid,
                    'appraiserid' => $appraiserid,
                ]);
                $commentstext = "";
                if (!empty($grade)) {
                    $comments = new stdClass();
                    $comments->criteriacomment = $this->format_text($grade->comment, $grade->commentformat);
                    $comments->appraisalcontext = $this->format_text($grade->appraisalcontext, $grade->appraisalcontextformat);
                    $comments->appraisalcomment = $this->format_text($grade->appraisalcomment, $grade->appraisalcommentformat);
                    $comments->appraisalid = $appraisalid;
                    $comments->appraiserid = $appraiserid;
                    $comments->criteriaid = $row->id;
                    $commentstext =
                        $renderer->render(new grade_widget($grade->grade, $subgradescount > 0, $comments));
                }
                $row->{$this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid)} = $commentstext;
            }
        }
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
            new hidden(['fieldname' => 'criterionparentid', 'rawtype' => PARAM_INT]),
            new hidden(['fieldname' => 'criterionsort', 'rawtype' => PARAM_INT]),
            new text(['fieldname' => 'criterionname', 'fullname' => get_string('criterion:label', 'local_cveteval')]),
        ];
        $planningsql = planning_entity::get_historical_sql_query("plan");
        $rolesql = role_entity::get_historical_sql_query("role");
        $situationsql = situation_entity::get_historical_sql_query("situation");
        $groupassignmentsql = group_assignment_entity::get_historical_sql_query("groupa");
        // Add columns only when filters are defined.
        if (!empty($this->filterset)) {
            global $DB;
            list($additionalwhere, $params) = $this->filterset->get_sql_for_filter('',
                array('criterionname'),
                $this->fieldaliases
            );
            $from = "{local_cveteval_appraisal} appraisal 
                LEFT JOIN $planningsql ON appraisal.evalplanid = plan.id
                LEFT JOIN $situationsql ON plan.clsituationid = situation.id
                LEFT JOIN $groupassignmentsql ON groupa.groupid = plan.groupid
                LEFT JOIN $rolesql ON plan.clsituationid = role.clsituationid
                LEFT JOIN (SELECT " . $DB->sql_concat_join("' '", array('u.firstname', 'u.lastname'))
                . " AS fullname, u.id FROM {user} u ) appraiser ON appraiser.id = appraisal.appraiserid";
            $fields = [];
            $fields[] = 'appraisal.id AS id';
            $fields[] = 'appraisal.appraiserid AS appraiserid';
            $fields[] = 'appraisal.studentid AS studentid';
            $fields[] = 'appraiser.fullname AS appraiserfullname';
            $fields[] = 'appraisal.timemodified AS appraisaldate';
            $appraisalsraws = $DB->get_records_sql('SELECT DISTINCT '
                . join(', ', $fields)
                . ' FROM ' . $from
                . ' WHERE plan.id IS NOT NULL AND (' . $additionalwhere . ') '
                . ' GROUP BY appraisal.id, appraisal.appraiserid, appraisal.studentid',
                $params);
            $this->appraiserlist = [];
            $appraiserinfo = [];
            foreach ($appraisalsraws as $appraisal) {
                if (empty($this->appraiserlist[$appraisal->id])) {
                    $this->appraiserlist[$appraisal->id] = $appraisal->appraiserid;
                    $date = userdate($appraisal->appraisaldate,
                        get_string('strftimedatefullshort', 'core_langconfig'));
                    $username = $appraisal->appraiserid ?
                            fullname(\core_user::get_user($appraisal->appraiserid)) : get_string('evaluation:waiting', 'local_cveteval');
                    $appraiserinfo[$appraisal->id] = $username
                        . " ({$date})";
                }
            }
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $this->fields[] = new html([
                    'fieldname' => $this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid),
                    'fullname' => $appraiserinfo[$appraisalid]
                ]);
            }
            // This is a somewhat tricky part: we cannot set the plan id and we need only
            // to retrieve criteria from the right situation evaluation grid.
            $planfilter = $this->filterset->get_filter('planid');
            [$planwhere, $planparams] = $planfilter->get_sql_filter('plan.id');
            $evalgridids = $DB->get_fieldset_sql("SELECT DISTINCT situation.evalgridid
                FROM $planningsql
                LEFT JOIN $situationsql ON plan.clsituationid = situation.id
                WHERE $planwhere", $planparams);
            [$this->evalgridwhere, $this->evalgridparams] = $DB->get_in_or_equal($evalgridids, SQL_PARAMS_NAMED);
        }
        $this->setup_other_fields();
    }

    protected function get_appraiser_appraisal_columnname($appraiserid, $appraisalid) {
        return "appraisergrade{$appraiserid}{$appraisalid}";
    }
}
