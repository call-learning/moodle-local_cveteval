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
        $fields[] = 'critapp.id AS id';
        $fields[] = 'critapp.appraisalid AS appraisalid';
        $fields[] = 'COALESCE(critapp.grade) AS grade';
        $fields[] = 'critapp.comment AS comment';
        $fields[] = 'criterion.sort AS sort';
        $fields[] = 'criterion.label AS label';
        $fields[] = 'criterion.parentid AS criterionparentid';
        $fields[] = 'critapp.timemodified AS datetime';
        $this->set_sql(join(', ', $fields), $from,'criterion.parentid = 0', []);
        // Just the first set, we will fold the other row in the result.
    }


    /**
     * Force SQL sort by criterionparentid, sort
     * @return string SQL fragment that can be used in an ORDER BY clause.
     */
    public function get_sql_sort() {
        return parent::get_sql_sort();
    }

    protected function col_grade($row) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_cveteval');
        return $renderer->render(new grade_widget($row->grade));
    }
}

