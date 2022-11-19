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
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\role\entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;

/**
 * Matcher implementation for group_assignment
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class role extends base {
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
        $userid = $newentity->get('userid');
        if (!$userid) {
            return [];
        }
        $params = [];
        $params['situationsn'] = $this->get_entity_field_name(
                $newentity->get('clsituationid'), $this->dm->get_dest_id(), "idnumber", situation_entity::class);
        $params['userid'] = $userid;
        $params['type'] = $newentity->get('type');

        $oldrolessql = entity::get_historical_sql_query_for_id("e", $this->dm->get_origin_id());
        $oldsituationssql = situation_entity::get_historical_sql_query_for_id("oldsituations", $this->dm->get_origin_id());
        $oldrolesid = $DB->get_fieldset_sql(
                "SELECT DISTINCT e.id
                FROM $oldrolessql LEFT JOIN $oldsituationssql ON e.clsituationid = oldsituations.id
                WHERE e.userid = :userid AND e.type = :type AND " .
                $DB->sql_equal('oldsituations.idnumber', ':situationsn', false, false),
                $params);

        $oldroles = [];
        if ($oldrolesid) {
            history_entity::disable_history();
            $oldroles = array_map(function($gid) {
                return new entity($gid);
            }, $oldrolesid);
        }
        return $oldroles;
    }
}
