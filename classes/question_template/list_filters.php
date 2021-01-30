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
 * Question template filter
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\question_template;

use local_cveteval\form\persistent_list_filter;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Persistent filters
 *
 * @package     local_cveteval
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class list_filters extends persistent_list_filter {

    /** @var string The fully qualified classname. */
    protected static $persistentclass = '\\local_cveteval\\question_template\\entity';

    /**
     * Form property in order to display the right widget for the form.
     *
     * @return array|array[]
     */
    public static function define_properties() {
        return array(
            'name' => (object) array(
                'sortable' => true,
            ),
            'idnumber' => (object) array(
                'sortable' => true,
            ),
            'version' => (object) array(
                'sortable' => true,
            )
        );
    }
}