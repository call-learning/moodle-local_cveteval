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

use coding_exception;
use context;
use local_cltools\local\field\datetime;
use local_cltools\local\field\editor;
use local_cltools\local\field\hidden;
use local_cltools\local\field\number;
use local_cltools\local\field\text;
use local_cltools\local\table\dynamic_table_sql;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\output\grade_widget;
use local_cveteval\roles;

/**
 * A list of appraisal criteria for a given appraisal
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class appraisals_criteria extends dynamic_table_sql {

    /**
     * Field definition
     */
    protected const FIELDS = [
            'critapp.id AS id',
            'critapp.appraisalid AS appraisalid',
            'COALESCE(critapp.grade, 0) AS grade',
            'critapp.comment AS comment',
            'criterion.sort AS sort',
            'criterion.label AS label',
            'criterion.parentid AS criterionparentid',
            'criterion.id AS criterionid',
            'COALESCE(critapp.timemodified,0) AS datetime'
    ];

    /**
     * Constructor
     *
     * @param int $uniqueid
     * @param array $actionsdefs
     * @param bool $editable
     */
    public function __construct($uniqueid = null,
            $actionsdefs = null,
            $editable = false) {
        $this->fieldaliases = [
                'appraisalid' => 'critapp.appraisalid'
        ];
        parent::__construct($uniqueid, $actionsdefs, $editable);
    }

    /**
     * Retrieve data from the database and return a row set
     *
     * @param int $pagesize
     * @return array
     */
    public function get_rows($pagesize) {
        global $DB;
        list($additionalwhere, $params) = $this->filterset->get_sql_for_filter();
        $where = '';
        if ($additionalwhere) {
            $where = " AND ($additionalwhere)";
        }
        $criterionsql = criterion_entity::get_historical_sql_query("criterion");
        $sql = 'SELECT DISTINCT ' . join(', ', static::FIELDS)
                . " FROM $criterionsql "
                . 'LEFT JOIN {local_cveteval_appr_crit} critapp ON  criterion.id = critapp.criterionid
               WHERE criterion.parentid = :parentcriterion ' . $where . ' ORDER BY sort';
        $this->setup();
        $this->query_db($pagesize, false);
        $rows = [];
        foreach ($this->rawdata as $row) {
            $params['parentcriterion'] = $row->criterionid;
            $subcriteria = $DB->get_records_sql($sql, $params);
            $formattedrow = $this->format_row($row);
            if ($subcriteria) {
                foreach ($subcriteria as $sub) {
                    $sub->grade = $this->col_grade($sub);
                }
                $formattedrow['_children'] = array_values($subcriteria);
            }
            $rows[] = (object) $formattedrow;
        }
        $this->close_recordset();
        return $rows;
    }

    /**
     * Grade column
     *
     * @param object $row
     * @return bool|string
     * @throws coding_exception
     */
    protected function col_grade($row) {
        global $PAGE;
        $renderer = $PAGE->get_renderer('local_cveteval');
        return $renderer->render(new grade_widget($row->grade));
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
                new hidden(['fieldname' => 'appraisalid', 'rawtype' => PARAM_INT]),
                new text(['fieldname' => 'appraisalid', 'rawtype' => PARAM_RAW,
                        'displayname' => get_string("criterion:label", 'local_cveteval')]),
                new number(['fieldname' => 'appraisalid',
                        'displayname' => get_string("appraisalcriterion:grade", 'local_cveteval')],
                        true),
                new editor(['fieldname' => 'comment', 'displayname' => get_string("appraisal:comment", 'local_cveteval')]),
                new datetime(['fieldname' => 'datetime',
                        'displayname' => get_string("appraisal:modificationdate", 'local_cveteval')]),
        ];
        $this->setup_other_fields();
    }

    /**
     * Get sql from query parts
     *
     * @param string $tablealias
     * @return string
     */
    protected function internal_get_sql_from($tablealias = 'e') {
        return '{local_cveteval_criterion} criterion
        LEFT JOIN {local_cveteval_appr_crit} critapp ON  criterion.id = critapp.criterionid';
    }

    /**
     * Get sql fields
     *
     * Overridable sql query
     *
     * @param string $tablealias
     */
    protected function internal_get_sql_fields($tablealias = 'e') {
        return "DISTINCT " . join(',', static::FIELDS) . " ";
    }

    /**
     * Get where
     *
     * @param bool $disablefilters
     * @param string $tablealias
     * @return array
     */
    protected function internal_get_sql_where($disablefilters = false, $tablealias = 'e') {
        [$where, $params] = parent::internal_get_sql_where($disablefilters);
        return ["{$where} AND criterion.parentid = 0 AND critapp.id IS NOT NULL", $params];
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
        return !roles::can_appraise($USER->id);
    }
}

