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
 * Clinical situation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\situation;
defined('MOODLE_INTERNAL') || die();

/**
 * Clinical situation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends \core\persistent {
    const TABLE = 'local_cveteval_clsituation';

    /**
     * Appraiser
     */
    const SITUATION_TYPE_APPRAISER='appraiser';

    /**
     * Student
     */
    const SITUATION_TYPE_STUDENT='student';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected static function define_properties() {
        return array(
            'title' => array(
                'type' => PARAM_TEXT,
                'default' => ''
            ),
            'description' => array(
                'type' => PARAM_RAW,
            ),
            'descriptionformat' => array(
                'type' => PARAM_INT,
                'default' => ''
            ),
            'idnumber' => array(
                'type' => PARAM_ALPHANUMEXT,
                'null' => NULL_NOT_ALLOWED
            ),
            'expectedevalsnb' => array(
                'type' => PARAM_INT,
                'default' => 1
            ),
            'evalgridid' => array(
                'type' => PARAM_INT,
                'default' => ''
            )
        );
    }
}

