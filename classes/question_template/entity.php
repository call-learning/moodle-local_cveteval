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
 * Question template entity
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveval\question_template;

defined('MOODLE_INTERNAL') || die();

/**
 * Class rotation
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends \core\persistent {
    const TABLE = 'local_competveteval_qtpl';

    const QUESTION_TYPE_FREE_TEXT = 0;
    const QUESTION_TYPE_LIKERT = 1;

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     */
    protected static function define_properties() {
        return array(
            'type' => array(
                'type' => PARAM_INT,
                'default' => '',
            ),
            'label' => array(
                'type' => PARAM_ALPHANUMEXT,
                'default' => '',
            ),
            'path' => array(
                'type' => PARAM_ALPHANUMEXT,
                'default' => '',
            )
        );
    }

    /**
     * Question type
     *
     * @return array
     * @throws \coding_exception
     */
    public static function get_question_types() {
        return  [
            self::QUESTION_TYPE_FREE_TEXT => get_string('qtype:freetext', 'local_cveval'),
            self::QUESTION_TYPE_LIKERT => get_string('qtype:likert', 'local_cveval')
        ];
    }
}
