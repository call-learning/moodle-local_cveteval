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

namespace local_cveteval\local\importer\grouping;
defined('MOODLE_INTERNAL') || die();

use tool_importer\field_types;
use tool_importer\importer_exception;
use \local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use \local_cveteval\local\persistent\group\entity as group_entity;

class data_importer extends \tool_importer\data_importer {

    private $grouping = [];
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
            if (preg_match('/groupement.*/', strtolower($name))) {
                $this->grouping[] = $name;
            }
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
        static $groups = null;

        $this->basic_validations($row);

        // Preload groups.
        if (empty($groups)) {
            $groupsrecords = group_entity::get_records();
            $groups = [];
            foreach($groupsrecords as $record) {
                $groups[$record->get('name')] = $record;
            }
        }

        $row = array_merge($this->defaultvalues, $row);

        $gassigments =  [];


        $email = clean_param(trim($row['email']), PARAM_EMAIL);
        $user = \core_user::get_user_by_email($email);
        if (!$user) {
            return false;
        } else {
            foreach ($this->grouping as $grouping) {
                if (!empty($row[$grouping])) {
                    if (!empty($groups[$row[$grouping]])) {
                        $group = $groups[$row[$grouping]];
                        $ga = group_assignment_entity::get_record(array(
                                'studentid' => $user->id,
                                'groupid' => $group->get('id')
                            )
                        );
                        if (!$ga) {
                            $ga = new group_assignment_entity(0, (object) array(
                                'studentid' => $user->id,
                                'groupid' => $group->get('id')
                            )
                            );
                            $ga->create();
                        }
                        $gassigments[] = $ga;
                    }

                }
            }
        }
        return $gassigments;
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
        $fielddef['email'] = [
            'type' => field_types::TYPE_TEXT,
            'required' => true
        ];

        foreach ($this->grouping as $groupingname) {
            $fielddef[$groupingname] = [
                'type' => field_types::TYPE_TEXT,
                'required' => false
            ];
        }
        return $fielddef;
    }
}


