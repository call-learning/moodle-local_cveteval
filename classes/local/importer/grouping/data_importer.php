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

use cache;
use cache_store;
use coding_exception;
use core\invalid_persistent_exception;
use core_user;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\utils;
use moodle_exception;
use tool_importer\local\exceptions\importer_exception;
use tool_importer\local\exceptions\validation_exception;

/**
 * Class data_importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_importer extends \tool_importer\data_importer {

    /**
     * Group cache name
     */
    const GROUP_CACHE_NAME = 'grouploadcache';
    public $groupassignmentcount = 0;
    public $groupcount = 0;
    private $grouping = [];

    /**
     * Called just before importation or validation.
     *
     * Gives a chance to reinit values or local information before a real import.
     *
     * @param mixed|null $options additional importer options
     */
    public function init($options = null) {
        foreach (array_keys($this->get_source()->get_fields_definition()) as $name) {
            if (preg_match('/groupement.*/', strtolower($name))) {
                $this->grouping[] = $name;
            }
        }
        $this->init_groups();
        $this->groupassignmentcount = 0;
        $this->groupcount = 0;
    }

    /**
     * Init group in cache
     *
     * @return void
     * @throws coding_exception
     */
    protected function init_groups() {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_cveteval', self::GROUP_CACHE_NAME);
        $cache->purge();
        $groupsrecords = group_entity::get_records();
        foreach ($groupsrecords as $record) {
            $cache->set($record->get('name'), $record->get('id'));
        }
    }

    /**
     * Check if row is valid after transformation.
     *
     *
     * @param array $row
     * @param int $rowindex
     * @param mixed|null $options import options
     * @throws validation_exception
     */
    public function validate_after_transform($row, $rowindex, $options = null) {
        utils::check_user_exists_or_multiple($row['email'], $rowindex, 'grouping:multipleuserfound', 'grouping:usernotfound',
                'email');
    }

    /**
     * Update or create planning entry.
     *
     * Prior to this we might also create a group so then students can be associated with
     * the group.
     *
     * @param array $row associative array storing the record
     * @param mixed|null $options import options
     * @return array
     * @throws importer_exception
     */
    protected function raw_import($row, $rowindex, $options = null) {
        $row = array_merge($this->defaultvalues, $row);
        $gassigments = [];
        foreach ($this->grouping as $grouping) {
            try {
                if (!empty($row[$grouping])) {
                    $group = $this->get_add_group($row[$grouping]);
                    $user = core_user::get_user_by_email($row['email']);
                    $ga = group_assignment_entity::get_record(array(
                                    'studentid' => $user->id,
                                    'groupid' => $group->get('id')
                            )
                    );
                    if (!$ga) {
                        $ga = new group_assignment_entity(0, (object) array(
                                'studentid' => $user->id,
                                'groupid' => $group->get('id')
                        ));
                        $ga->create();
                        $this->groupassignmentcount++;
                    }
                    $gassigments[] = $ga;
                }

            } catch (moodle_exception $e) {
                throw new importer_exception(
                        'grouping:error', $rowindex, '', 'local_cveteval', $grouping);
            }
        }
        return $gassigments;
    }

    /**
     * Add group or get the related group from its name.
     *
     * @param string|null $groupname
     * @return false|group_entity
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    protected function get_add_group($groupname = null) {
        // If found in cache.
        $groupname = clean_param(trim($groupname), PARAM_TEXT);
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_cveteval', self::GROUP_CACHE_NAME);
        if ($cache->has($groupname)) {
            return group_entity::get_record(['id' => $cache->get($groupname)]);
        }
        $group = new group_entity(0, (object) ['name' => $groupname]);
        $group->create();
        $this->groupcount++;
        $cache->set($groupname, $group->get('id'));
        return $group;
    }
}



