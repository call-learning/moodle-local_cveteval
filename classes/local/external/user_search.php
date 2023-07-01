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
namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();
global $CFG;
use core_user;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_warnings;

require_once($CFG->dirroot . '/user/externallib.php');

/**
 * External services : user profile
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_search extends \external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(core_user::get_property_type('id'), 'ID of the user'),
                            'fullname' => new external_value(core_user::get_property_type('firstname'), 'The fullname of the user'),
                            'email' => new external_value(core_user::get_property_type('email'),
                                'An email address - allow email as root@localhost', VALUE_OPTIONAL),
                        ]
                    )
                ),
                'warnings' => new external_warnings('always set to \'key\'', 'faulty key name')
            ]
        );
    }

    /**
     * Returns user profile
     *
     * @param string $search
     * @return array
     */
    public static function execute($search): array {
        $users = \core_user::search($search);
        $returnedusers = [];

        foreach ($users as $user) {
            $returnedusers[] = (object) [
                'id' => $user->id,
                'fullname' => fullname($user),
                'email' => $user->email,
            ];
        }
        return [
            'users' => $returnedusers,
            'warnings' => array(),
        ];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            array(
                'search' => new external_value(PARAM_TEXT, 'the search value'),
            )
        );
    }
}
