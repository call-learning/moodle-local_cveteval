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

namespace local_cveteval\local\datamigration\matchers;

use core\persistent;
use core_user;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity;

/**
 * Matcher implementation for group_assignment
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group_assignment extends base {

    /**
     * Get entity
     *
     * @return string
     */
    public static function get_entity() {
        return entity::class;
    }

    /**
     * Internal: Try to match a given model/entity type
     *
     * The current active history is the origin
     *
     * @param persistent $newentity
     * @return persistent|persistent[]|false
     */
    public function do_match(persistent $newentity) {
        global $DB;
        $student = core_user::get_user($newentity->get('studentid'));
        if (!$student) {
            return [];
        }
        $newgroupname = $this->get_entity_field_name(
                $newentity->get('groupid'), $this->dm->get_dest_id(), "name", group_entity::class);

        $oldgroupsql = group_entity::get_historical_sql_query_for_id("oldgroup", $this->dm->get_origin_id());
        $olgroupassignsql = entity::get_historical_sql_query_for_id("e", $this->dm->get_origin_id());
        $oldgroupassign = $DB->get_fieldset_sql(
                "SELECT DISTINCT e.id
                FROM $olgroupassignsql LEFT JOIN $oldgroupsql ON e.groupid = oldgroup.id
                WHERE e.studentid = :studentid AND " .
                $DB->sql_equal('oldgroup.name', ':name', false, false),
                ['name' => trim($newgroupname), 'studentid' => $student->id]);
        return array_map(function($gid) {
            return new entity($gid);
        }, $oldgroupassign);
    }

}
