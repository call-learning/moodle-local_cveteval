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
 * Evaluation template entity edit or add form
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveval\evaluation_template;

use local_cveval\utils\persistent_list;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Add Form
 *
 * @package     local_cveval
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entities_list extends persistent_list {
    protected static $persistentclass = '\\local_cveval\\evaluation_template\\entity';

    /**
     * List columns
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    public static function define_properties() {
        $props = array(
            'name' => (object) array(
                'fullname' => get_string('name', 'local_cveval'),
            ),
            'idnumber' => (object) array(
                'fullname' => get_string('idnumber', 'local_cveval'),
            )
        );
        self::add_all_definition_from_persistent($props);
        return $props;
    }
}
