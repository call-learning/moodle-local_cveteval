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
use local_cveteval\local\persistent\criterion\entity;
use local_cveteval\local\persistent\history\entity as history_entity;

/**
 * Matcher implementation for evaluation_grid
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class criterion extends base {

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
        history_entity::set_current_id($this->dm->get_dest_id());
        $parentidnumber = null;
        if (!empty($newentity->get('parentid'))) {
            $parententity = entity::get_records(['id' => $newentity->get('parentid')]);
            // If the default grid has the same parentid, we take the one from the grid used in the upload.
            if (count($parententity) >= 2) {
                history_entity::set_current_id($this->dm->get_dest_id(), true);
                $parententity = entity::get_record(['id' => $newentity->get('parentid')]);
            } else {
                $parententity = array_shift($parententity);
            }
            $parentidnumber = $parententity->get('idnumber');
        }
        history_entity::set_current_id($this->dm->get_origin_id());
        $params = ['idnumber' => $newentity->get('idnumber')];
        if ($parentidnumber) {
            $oldparententity = entity::get_records(['idnumber' => $parentidnumber]);
            if (count($oldparententity) >= 2) {
                history_entity::set_current_id($this->dm->get_origin_id(), true);
                $oldparententity = entity::get_record(['idnumber' => $parentidnumber]);
            } else {
                $oldparententity = array_shift($oldparententity);
            }
            $params['parentid'] = $oldparententity->get('id');
        }
        $criterion = entity::get_records($params);
        if (count($criterion) >= 2) {
            history_entity::set_current_id($this->dm->get_origin_id(), true);
            $criterion = entity::get_records($params);
        }
        return $criterion;
    }
}
