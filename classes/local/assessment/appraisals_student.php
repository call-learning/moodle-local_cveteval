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
use local_cltools\local\field\base;
use local_cltools\local\filter\filterset;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\output\grade_widget;
use ReflectionException;
use stdClass;
use table_sql;

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
     * appraisals_student constructor.
     *
     * @param $uniqueid
     * @throws coding_exception
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // A  bit of a hack here. We use this on subqueries only...
        $this->fieldaliases = [
            'planid' => 'plan.id',
            'studentid' => 'appraisal.studentid',
            'roletype' => 'role.type',
            'criterionname' => 'criterion.label'
        ];
    }

    /**
     * Set the filterset in the table class.
     * As columns are dependent on filters, then we need to update column definition also.
     *
     * The use of filtersets is a requirement for dynamic tables, but can be used by other tables too if desired.
     * This also sets the filter aliases if not set for each filters, depending on what is set in the
     * local $filteralias array.
     *
     * @param filterset $filterset The filterset object to get filters and table parameters from
     */
    public function set_extended_filterset(filterset $filterset): void {
        parent::set_extended_filterset($filterset);
        list($cols, $headers) = $this->get_table_columns_definitions();
        $this->define_columns($cols);
        $this->define_headers($headers);
        $this->set_initial_sql();

    }

    /**
     * Set SQL parameters (where, from,....) from the entity
     *
     * We just retrieve the criteria here and we will gather the rest after.
     * This can be overridden when we are looking at linked entities.
     */
    protected function set_initial_sql() {
        $from = '{local_cveteval_criterion} criterion';
        $fields = static::FIELDS;
        if ($this->appraiserlist) {
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $fields[] = " '' AS " . $this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid);
            }
        }
        $this->set_sql(join(', ', $fields), $from, '1=1', []);
    }

    /**
     * Here we go back to the original setup for a table query
     * We just return the row and we will enrich the information with the
     * relevant data. That is to say, this is really hack.
     * The filters will be ignored.
     *
     * @param int $pagesize
     * @param bool $useinitialsbar
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        table_sql::query_db($pagesize, $useinitialsbar);
    }

    /**
     * Retrieve data from the database and return a row set
     * This is a complete hack here as we have had to transpose the table from the original
     * design.
     *
     * @return array
     */
    public function retrieve_raw_data($pagesize) {
        $rows = parent::retrieve_raw_data($pagesize);
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
     * Get appraisal criteria grade
     *
     * @param $row
     * @throws dml_exception
     * @throws coding_exception
     */
    public function get_appraisal_criteria_grade(&$row) {
        global $DB;
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_cveteval');
        if ($this->appraiserlist) {
            list($additionalwhere, $params) = $this->filterset->get_sql_for_filter('',
                array('criterionname')
            );
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $grade = $DB->get_record_sql(
                    "SELECT c.criterionid AS criterionid, c.grade AS grade,
                        c.comment AS comment, c.commentformat,
                        appraisal.comment AS appraisalcomment, appraisal.commentformat AS appraisalcommentformat,
                        appraisal.context AS appraisalcontext, appraisal.contextformat AS appraisalcontextformat
                    FROM {local_cveteval_appr_crit} c
                    LEFT JOIN {local_cveteval_criterion} criterion ON criterion.id = c.criterionid
                    LEFT JOIN {local_cveteval_appraisal} appraisal ON appraisal.id = c.appraisalid
                    LEFT JOIN {local_cveteval_evalplan} plan ON plan.id = appraisal.evalplanid
                    LEFT JOIN {local_cveteval_role} role ON plan.clsituationid = role.clsituationid  
                        AND role.userid = appraisal.appraiserid
                    WHERE appraisal.appraiserid = :appraiserid AND c.appraisalid = :appraisalid
                    AND c.criterionid = :criterionid
                    AND $additionalwhere",
                    $params + [
                        'appraiserid' => $appraiserid,
                        'criterionid' => $row->id,
                        'appraisalid' => $appraisalid
                    ]

                );
                $subgradescount = $DB->count_records_sql('
                    SELECT COUNT(c.id)
                    FROM {local_cveteval_appr_crit} c
                    LEFT JOIN {local_cveteval_criterion} criterion ON criterion.id = c.criterionid
                    LEFT JOIN {local_cveteval_appraisal} appraisal ON appraisal.id = c.appraisalid
                    WHERE criterion.parentid = :criterionid AND appraisal.appraiserid = :appraiserid AND c.appraisalid = :appraisalid
                    ', [
                    'criterionid' => $row->id,
                    'appraisalid' => $appraisalid,
                    'appraiserid' => $appraiserid,
                ]);
                $comments = new stdClass();
                $comments->criteriacomment = $this->format_text($grade->comment, $grade->commentformat);
                $comments->appraisalcontext = $this->format_text($grade->appraisalcontext, $grade->appraisalcontextformat);
                $comments->appraisalcomment = $this->format_text($grade->commentformat, $grade->appraisalcommentformat);
                $row->{$this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid)} =
                    $renderer->render(new grade_widget($grade->grade, $subgradescount > 0, $comments));
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
        $colfields = [
            'id' => [
                "fullname" => 'criterionid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'criterionparentid' => [
                "fullname" => "criterionparentid",
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'criterionsort' => [
                "fullname" => "criterionsort",
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'criterionname' => [
                "fullname" => get_string('criterion:label', 'local_cveteval'),
                "rawtype" => PARAM_TEXT,
                "type" => "text"
            ]
        ];
        // Add columns only when filters are defined.
        if (!empty($this->filterset)) {
            global $DB;
            list($additionalwhere, $params) = $this->filterset->get_sql_for_filter('',
                array('criterionname')
            );
            $from = '
                {local_cveteval_appraisal} appraisal
                LEFT JOIN {local_cveteval_evalplan} plan ON appraisal.evalplanid = plan.id
                LEFT JOIN {local_cveteval_group_assign} groupa ON groupa.groupid = plan.groupid
                LEFT JOIN {local_cveteval_role} role ON plan.clsituationid = role.clsituationid
                LEFT JOIN (SELECT ' . $DB->sql_concat_join("' '", array('u.firstname', 'u.lastname'))
                . ' AS fullname, u.id FROM {user} u ) appraiser ON appraiser.id = appraisal.appraiserid';
            $fields = [];
            $fields[] = 'appraisal.id AS id';
            $fields[] = 'appraisal.appraiserid AS appraiserid';
            $fields[] = 'appraisal.studentid AS studentid';
            $fields[] = 'appraiser.fullname AS appraiserfullname';
            $fields[] = 'appraisal.timemodified AS appraisaldate';
            $appraisalsraws = $DB->get_records_sql('SELECT DISTINCT '
                . join(', ', $fields)
                . ' FROM ' . $from
                . ' WHERE 1=1 AND (' . $additionalwhere . ') '
                . ' GROUP BY appraisal.id, appraisal.appraiserid, appraisal.studentid',
                $params);
            $this->appraiserlist = [];
            $appraiserinfo = [];
            foreach ($appraisalsraws as $appraisal) {
                if (empty($this->appraiserlist[$appraisal->id])) {
                    $this->appraiserlist[$appraisal->id] = $appraisal->appraiserid;
                    $date = userdate($appraisal->appraisaldate,
                        get_string('strftimedatefullshort', 'core_langconfig'));
                    $appraiserinfo[$appraisal->id] = fullname(\core_user::get_user($appraisal->appraiserid))
                        . " ({$date})";
                }
            }
            foreach ($this->appraiserlist as $appraisalid => $appraiserid) {
                $colfields[$this->get_appraiser_appraisal_columnname($appraiserid, $appraisalid)] = [
                    "fullname" => $appraiserinfo[$appraisalid],
                    "rawtype" => PARAM_RAW,
                    "type" => "html" // List of grades separated by comma (grades and subcriteria grades).
                ];
            }
        }
        $this->fields = [];
        foreach ($colfields as $name => $prop) {
            $this->fields[$name] = base::get_instance_from_def($name, $prop);
        }
        $this->setup_other_fields();
    }

    protected function get_appraiser_appraisal_columnname($appraiserid, $appraisalid) {
        return 'appraisergrade' . ($appraiserid + $appraisalid);
    }
}

