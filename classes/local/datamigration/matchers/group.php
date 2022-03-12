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
use local_cveteval\local\persistent\group\entity;

/**
 * Matcher implementation for group
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class group extends base {

    public static function get_entity() {
        return entity::class;
    }

    /**
     * Try to match a given model/entity type
     *
     * @return persistent|persistent[]|false
     */
    public function do_match(persistent $newentity) {
        global $DB;
        return entity::get_records_select($DB->sql_equal('name', ':name', false, false),
                ['name' => trim($newentity->get('name'))]);
    }
}
