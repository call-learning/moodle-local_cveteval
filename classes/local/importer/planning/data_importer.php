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
 * Grouping Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\planning;
defined('MOODLE_INTERNAL') || die();

use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use tool_importer\field_types;
use tool_importer\importer_exception;

class data_importer extends \tool_importer\data_importer {

    protected $groups = [];

    /**
     * data_importer constructor.
     *
     * @param null $defaultvals additional default values
     * @throws \dml_exception
     */
    public function __construct($defaultvals = null, $fielddefinition) {
        $this->defaultvalues = [];
        if ($defaultvals) {
            $this->defaultvalues = array_merge($this->defaultvalues, $defaultvals);
        }
        foreach (array_keys($fielddefinition) as $name) {
            if (preg_match('/groupe.*/', strtolower($name))) {
                $this->groups[] = $name;
            }
        }
        $this->add_groups();
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
        static $groups = null;
        static $clsituations = null;

        $this->basic_validations($row);

        $row = array_merge($this->defaultvalues, $row);

        // Preload groups and clinical situations.
        if (!$groups) {
            foreach ($this->groups as $groupname) {
                $groups[$groupname] = group_entity::get_record(['name' => $groupname]);
            }
        }
        if (!$clsituations) {
            $clsituationsrecords = situation_entity::get_records();
            $clsituations = [];
            foreach($clsituationsrecords as $record) {
                $clsituations[$record->get('idnumber')] = $record;
            }
        }
        // Now the row and add a planning instance for each group and clinical situation.
        $plannings = [];
        foreach($groups as $groupname => $group) {
            $record = new \stdClass();
            $record->starttime = $row['starttime'];
            $record->endtime = $row['endtime'];
            if (!empty($row[$groupname]) && !empty($clsituations[$row[$groupname]])) {
                $record->groupid = $group->get('id');
                $record->clsituationid = $clsituations[$row[$groupname]]->get('id');
                $planning  = new planning_entity(0, $record);
                $planning->create();
                $plannings[] = $planning;
            }
        }
        return $plannings;
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
        $fielddef = [];
        $fielddef['starttime'] = [
            'type' => field_types::TYPE_INT,
            'required' => true
        ];
        $fielddef['starttime'] = [
            'type' => field_types::TYPE_INT,
            'required' => true
        ];

        foreach ($this->groups as $groupname) {
            $fielddef[$groupname] = [
                'type' => field_types::TYPE_TEXT,
                'required' => false
            ];
        }
        return $fielddef;
    }

    public function add_groups() {
        foreach ($this->groups as $groupname) {
            $groupname = clean_param(trim($groupname), PARAM_TEXT);
            if (!group_entity::get_record(['name' => $groupname])) {
                $group = new group_entity(0, (object) ['name' => $groupname]);
                $group->create();
            }
        }
    }
}



