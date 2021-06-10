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

use external_function_parameters;
use external_single_structure;
use external_value;
use external_api;
use moodle_url;

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
     * @return external_multiple_structure
     */
    public static function idp_list_returns() {
        return new \external_multiple_structure(
                    new external_single_structure(
                        array(
                            'url' => new external_value(PARAM_RAW, 'URL to launch IDP connexion',
                                VALUE_OPTIONAL),
                            'name' => new external_value(PARAM_TEXT, 'IDP fullname'),
                            'iconurl' => new external_value(PARAM_RAW, 'IDP icon url', VALUE_OPTIONAL),
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
            $currentidplist = $authplugin->loginpage_idp_list(utils::get_application_launch_url([]));
            foreach($currentidplist  as $index => $idp) {
                if( $auth == 'cas') {
                    $idp['url'] = (new moodle_url('/local/cveteval/login/cas-login.php', array('authCAS' => 'CAS')))->out();
                } else {
                    $idp['url'] = $idp['url']->out();
                }
                $idp['iconurl'] = $idp['iconurl']->out();
                $currentidplist[$index] = $idp;
            }
            if ($currentidplist) {
                $idplist = array_merge($currentidplist, $idplist);
            }
        }
       return $idplist;
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

