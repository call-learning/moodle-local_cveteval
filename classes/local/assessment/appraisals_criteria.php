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
use local_cveteval\output\grade_widget;

/**
 * A list of student matching this situation
 *
 * @package   local_cltools
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appraisals_criteria extends dynamic_table_sql {

    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->filteraliases = [
            'appraisalid' => 'critapp.appraisalid'
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
                "fullname" => 'id',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'appraisalid' => [
                "fullname" => 'appraisalid',
                "rawtype" => PARAM_INT,
                "type" => "hidden"
            ],
            'label' => [
                "fullname" => get_string("criterion:label", 'local_cveteval'),
                "rawtype" => PARAM_RAW,
                "type" => "text"
            ],
            'grade' => [
                "fullname" => get_string("appraisalcriteria:grade", 'local_cveteval'),
                "rawtype" => PARAM_FLOAT,
                "type" => "html"
            ],
            'comment' => [
                "fullname" => get_string("appraisal:comment", 'local_cveteval'),
                "rawtype" => PARAM_RAW,
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

    protected const FIELDS = [
        'critapp.id AS id',
        'critapp.appraisalid AS appraisalid',
        'COALESCE(critapp.grade) AS grade',
        'critapp.comment AS comment',
        'criterion.sort AS sort',
        'criterion.label AS label',
        'criterion.parentid AS criterionparentid',
        'criterion.id AS criterionid',
        'critapp.timemodified AS datetime'
    ];

    /**
     * Set SQL parameters (where, from,....) from the entity
     *
     * This can be overridden when we are looking at linked entities.
     */
    protected function set_entity_sql() {
        global $DB;
        $from = '
        {local_cveteval_criteria} criterion
        LEFT JOIN {local_cveteval_appr_crit} critapp ON  criterion.id = critapp.criteriaid
        ';

        $this->set_sql(join(', ', static::FIELDS), $from,'criterion.parentid = 0', []);
        // Just the first set, we will fold the other row in the result.
    }


    /**
     * Force SQL sort by criterionparentid, sort
     * @return string SQL fragment that can be used in an ORDER BY clause.
     */
    public function get_sql_sort() {
        return parent::get_sql_sort();
    }

    /**
     * Grade column
     *
     * @param $row
     * @return bool|string
     * @throws \coding_exception
     */
    protected function col_grade($row) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_cveteval');
        return $renderer->render(new grade_widget($row->grade));
    }

    /**
     * Retrieve data from the database and return a row set
     *
     * @return array
     */
    public function retrieve_raw_data($pagesize) {
        global $DB;
        list($additionalwhere, $params) = $this->filterset->get_sql_for_filter();
        $where = '';
        if ($additionalwhere) {
            $where = " AND ($additionalwhere)";
        }
        $sql = 'SELECT DISTINCT ' . join(', ', static::FIELDS)
            . ' FROM {local_cveteval_criteria} criterion
               LEFT JOIN {local_cveteval_appr_crit} critapp ON  criterion.id = critapp.criteriaid
               WHERE criterion.parentid = :parentcriterion '. $where . ' ORDER BY sort' ;
        $rows = [];
        $this->setup();
        $this->query_db($pagesize, false);
        $rows = [];
        foreach ($this->rawdata as $row) {
            $params['parentcriterion'] =  $row->criterionid;
            $subcriteria = $DB->get_records_sql($sql, $params);
            $formattedrow = $this->format_row($row);
            if ($subcriteria) {
                foreach($subcriteria as &$sub) {
                    $sub->grade = $this->col_grade($sub);
                }
                $formattedrow['_children'] = array_values($subcriteria);
            }
            $rows[] = (object) $formattedrow;
        }
        $this->close_recordset();
        return $rows;
    }
}

