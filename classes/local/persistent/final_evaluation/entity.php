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
 * Final evaluation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\final_evaluation;

defined('MOODLE_INTERNAL') || die();

/**
 * Final evaluation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends \core\persistent {

    const TABLE = 'local_cveteval_finalevl';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected static function define_properties() {
        return array(
            'studentid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'appraiserid' => array(
                'type' => PARAM_INT,
                'default' => 0
            ),
            'evalplanid' => array(
                'type' => PARAM_INT,
                'default' => ''
            ),
            'grade' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'fullname' => get_string('finalevl:grade', 'local_cveteval')
                ]
            ),
            'context' => array(
                'type' => PARAM_TEXT,
                'default' => ''
            ),
            'contextformat' => array(
                'type' => PARAM_INT,
                'default' => FORMAT_PLAIN
            ),
            'comment' => array(
                'type' => PARAM_TEXT,
                'default' => ''
            ),
            'commentformat' => array(
                'type' => PARAM_INT,
                'default' => FORMAT_PLAIN
            ),
        );
    }
}
