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
 * External services : user profile
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\external;
defined('MOODLE_INTERNAL') || die();

use context_system;
use context_user;
use core_user;
use external_function_parameters;
use external_single_structure;
use external_value;

use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\utils;
use external_api;
use stdClass;
use user_picture;

/**
 * Get user type
 * Class user_type
 *
 * @package local_cveteval\local\external
 */
class user_profile extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            array(
                'userid' => new external_value(PARAM_INT, 'id type of user'),
                'fullname' => new external_value(PARAM_TEXT, 'user fullname'),
                'firstname' => new external_value(PARAM_TEXT, 'user fullname'),
                'lastname' => new external_value(PARAM_TEXT, 'user fullname'),
                'username' => new external_value(PARAM_ALPHANUMEXT, 'username', VALUE_OPTIONAL),
                'userpictureurl' => new external_value(PARAM_URL, 'user picture (avatar)',
                    VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Return the current information for the user
     */
    public static function execute($userid) {
        global $USER, $PAGE;
        self::validate_parameters(self::execute_parameters(), array('userid' => $userid));
        self::validate_context(context_system::instance());
        $user = core_user::get_user($userid);
        $context = context_user::instance($userid);
        $userinfo = new stdClass();
        $userinfo->fullname = fullname($user);
        $canseeadvanced = true;
        if ($userid != $USER->id and !has_capability('moodle/user:viewdetails', $context)) {
            $canseeadvanced = false;
        }
        $userpicture = new user_picture($user);
        $userpicture->includetoken = true;
        $userpicture->size = 1; // Size f1.
        $userinfo->studentname = fullname($user);

        $userinfo->studentpictureurl = $userpicture->get_url($PAGE)->out(false);
        return (object) [
            'userid' => $userid,
            'fullname' => fullname($user),
            'firstname' => $canseeadvanced ? $user->firstname : '',
            'lastname' => $canseeadvanced ? $user->lastname : '',
            'username' => $canseeadvanced ? $user->username : 'anonymous',
            'userpictureurl' => $userpicture->get_url($PAGE)->out(false)
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
                'userid' => new external_value(PARAM_INT, 'id of the user', VALUE_REQUIRED, NULL_NOT_ALLOWED)
            )
        );
    }
}

