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
 * Evaluation grid
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\evaluation_grid;

use coding_exception;
use context;
use core\invalid_persistent_exception;
use core\persistent;
use core\task\manager;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\text;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\model_with_history_impl;
use local_cveteval\roles;
use local_cveteval\task\upload_default_criteria_grid;

/**
 * Evaluation grid entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent, model_with_history {
    use enhanced_persistent_impl;
    use model_with_history_impl;

    /**
     * TABLE
     */
    const TABLE = 'local_cveteval_evalgrid';

    /**
     * DEFAULT GRID SHORTNAME
     */
    const DEFAULT_GRID_SHORTNAME = 'DEFAULTGRID';

    public static function define_fields(): array {
        return [
                new text(['fieldname' => 'name', 'editable' => true]),
                new text(['fieldname' => 'idnumber', 'rawtype' => PARAM_ALPHANUMEXT])
        ];
    }

    /**
     * Get default grid and create it if it does not exist.
     *
     * @return entity
     * @throws coding_exception
     * @throws invalid_persistent_exception
     */
    public static function get_default_grid() {
        $evalgrid = self::get_record(['idnumber' => self::DEFAULT_GRID_SHORTNAME]);
        if (!$evalgrid) {
            $evalgrid = new evaluation_grid_entity(0, (object) [
                    'name' => get_string('evaluationgrid:default', 'local_cveteval'),
                    'idnumber' => self::DEFAULT_GRID_SHORTNAME
            ]);
            // Create it and upload the criteria.
            $evalgrid->create();
            $task = new upload_default_criteria_grid();
            manager::queue_adhoc_task($task, true); // Upload default grid.
            return $evalgrid;
        } else {
            return $evalgrid;
        }

    }

    /**
     * Validate entity context
     *
     * @param $entityclass
     * @param $context
     * @return false|mixed
     */
    public function validate_access(context $context) {
        global $USER;
        return roles::can_assess($USER->id);
    }
}

