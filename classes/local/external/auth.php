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
class auth extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_single_structure
     */
    public static function idp_list_returns() {
        return new external_single_structure(
            array(
                'idplist' => new \external_multiple_structure(
                    new external_single_structure(
                        array(
                            'url' => new external_value(PARAM_RAW, 'URL to launch IDP connexion',
                                VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_TEXT, 'IDP fullname'),
                            'iconurl' => new external_value(PARAM_TEXT, 'IDP icon url', VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    /**
     * Return the current information for the user
     */
    public static function idp_list() {
        $authsenabled = get_enabled_auth_plugins();
        $idplist = [];
        foreach ($authsenabled as $auth) {
            $authplugin = get_auth_plugin($auth);
            $currentidplist = $authplugin->loginpage_idp_list('');
            foreach($currentidplist  as $index => $idp) {

                $idp['url'] = $idp['url']->out();
                $currentidplist[$index] = $idp;
            }
            if ($currentidplist) {
                $idplist = array_merge($currentidplist, $idplist);
            }
        }
       return (object) ['idplist' => $idplist];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function idp_list_parameters() {
        return new \external_function_parameters([]);
    }
}

