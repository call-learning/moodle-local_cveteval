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
 * Evaluation group_assignment
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\group_assignment;

use coding_exception;
use core\persistent;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\model_with_history_impl;

/**
 * Evaluation group_assignment entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements model_with_history {

    use model_with_history_impl;

    const TABLE = 'local_cveteval_group_assign';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws coding_exception
     */
    protected static function define_properties() {
        return array(
                'studentid' => array(
                        'type' => PARAM_INT,
                        'default' => '',
                        'format' => [
                                'type' => 'user_selector',
                                'selector_info' => (object) [
                                        'rolesn' => 'student'
                                ]
                        ]
                ),
                'groupid' => array(
                        'type' => PARAM_INT,
                        'default' => '',
                        'format' => [
                                'type' => 'entity_selector',
                                'selector_info' => (object) [
                                        'entity_type' => '\\local_cveteval\\local\\persistent\\group\\entity',
                                        'display_field' => 'name'
                                ]
                        ]
                ),
        );
    }
}
