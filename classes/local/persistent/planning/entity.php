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
 * Evaluation planning
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\planning;

use core\persistent;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\datetime;
use local_cltools\local\field\entity_selector;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\model_with_history_impl;

/**
 * Evaluation planning entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent, model_with_history {
    use model_with_history_impl;
    use enhanced_persistent_impl;

    const TABLE = 'local_cveteval_evalplan';

    public static function define_fields(): array {
        return [
                new entity_selector(
                        [
                                'fieldname' => 'groupid',
                                'entityclass' => \local_cveteval\local\persistent\group\entity::class,
                                'displayfield' => 'name'
                        ]
                ),
                new entity_selector(
                        [
                                'fieldname' => 'clsituationid',
                                'entityclass' => \local_cveteval\local\persistent\situation\entity::class,
                                'displayfield' => 'idnumber'
                        ]
                ),
                new datetime('starttime'),
                new datetime('endtime'),
        ];
    }

    /**
     * Get printable version of start time
     *
     * @return string
     */
    public function get_starttime_string() {
        return userdate($this->raw_get('starttime'), get_string('strftimedate', 'core_langconfig'));
    }

    /**
     * Get printable version of end time
     *
     * @return string
     */
    public function get_endtime_string() {
        return userdate($this->raw_get('endtime'), get_string('strftimedate', 'core_langconfig'));
    }

    public function get_context() {
        // TODO: Implement get_context() method.
    }
}
