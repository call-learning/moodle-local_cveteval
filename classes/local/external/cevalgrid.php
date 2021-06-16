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
 * External services
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();

use external_multiple_structure;
use external_single_structure;
use external_value;
/**
 * Class cevalgrid
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cevalgrid extends base_get_entity {

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    public static function get_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id of the evalgrid cevalgrid'),
                    'criterionid' => new external_value(PARAM_INT, 'id of the criterion'),
                    'evalgridid' => new external_value(PARAM_INT, 'id of the evalgrid'),
                    'sort' => new external_value(PARAM_INT, 'sortorder the evalgrid'),
                    'timemodified' => new external_value(PARAM_INT, 'last modification time'),
                    'timecreated' => new external_value(PARAM_INT, 'last modification time'),
                    'usermodified' => new external_value(PARAM_INT, 'user modified'),
                )
            )
        );
    }

}








