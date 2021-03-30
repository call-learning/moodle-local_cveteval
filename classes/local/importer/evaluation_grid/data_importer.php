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
 * Evaluation Grid Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\evaluation_grid;
defined('MOODLE_INTERNAL') || die();

use \local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use \local_cveteval\local\persistent\criteria\entity as criteria_entity;
use tool_importer\field_types;
use tool_importer\importer_exception;

/**
 * Class data_importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_importer extends \tool_importer\data_importer {
    /**
     * data_importer constructor.
     *
     * @param null $defaultvals additional default values
     * @throws \dml_exception
     */
    public function __construct($defaultvals = null) {
        $this->defaultvalues = [];
        if ($defaultvals) {
            $this->defaultvalues = array_merge($this->defaultvalues, $defaultvals);
        }
    }

    /**
     * Update or create planning entry.
     *
     * Prior to this we might also create a group so then students can be associated with
     * the group.
     *
     * @param array $row associative array storing the record
     * @return mixed|void
     * @throws importer_exception
     */
    protected function raw_import($row) {
        global $DB;
        $this->basic_validations($row);

        $row = array_merge($this->defaultvalues, $row);

        $evalgrid = evaluation_grid_entity::get_record(array('idnumber' => $row['evalgridid']));
        // Create one if it does not exist.
        if (!$evalgrid) {
            $evalgrid = new evaluation_grid_entity(0, (object) [
                'name' => get_string('evaluationgrid:default', 'local_cveteval'),
                'idnumber' => $row['evalgridid']
            ]);
            $evalgrid->create();
        }

        $criteriarecord = new \stdClass();
        $criteriarecord->label = $row['label'];
        $criteriarecord->idnumber = $row['idnumber'];
        $parentcriteria = criteria_entity::get_record(['idnumber' => $row['parentidnumber']]);
        $parentid = $parentcriteria ? $parentcriteria->get('id') : 0;
        $criteriarecord->parentid = $parentid;
        $criteriarecord->sort = criteria_entity::count_records(['parentid' => $parentid]) + 1;
        $criteria = new criteria_entity(0, $criteriarecord);
        $criteria->create();

        // Here we do without persistent class as it is just a link table.
        $cevalgridrecord = new \stdClass();
        $cevalgridrecord->criteriaid = $criteria->get('id');
        $cevalgridrecord->evalgridid = $evalgrid->get('id');
        $cevalgridrecord->sort =
            $DB->count_records('local_cveteval_cevalgrid', array('evalgridid' => $cevalgridrecord->evalgridid)) + 1;
        $DB->insert_record('local_cveteval_cevalgrid', $cevalgridrecord);

        return $criteriarecord;
    }

    /**
     * Get the field definition array
     *
     * The associative array has at least a series of column names
     * Types are derived from the field_types class
     * 'fieldname' => [ 'type' => TYPE_XXX, ...]
     *
     * @return array
     * @throws \coding_exception
     */
    public function get_fields_definition() {
        return [
            'evalgridid' => [
                'type' => field_types::TYPE_TEXT,
                'required' => true
            ],
            'idnumber' => [
                'type' => field_types::TYPE_TEXT,
                'required' => true
            ],
            'parentidnumber' => [
                'type' => field_types::TYPE_TEXT,
                'required' => false
            ],
            'label' => [
                'type' => field_types::TYPE_TEXT,
                'required' => true
            ]
        ];
    }
}



