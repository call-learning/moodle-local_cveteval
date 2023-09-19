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
 * Return token or information ref. SSO
 *
 * @package    local_cveteval
 *
 * Inspired from the login/token.php file and modified
 * according to our needs:
 *  - the cveteval application can create tokens
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
define('REQUIRE_CORRECT_ACCESS', true);
define('NO_MOODLE_COOKIES', true);

use core\session\manager;
use local_cveteval\utils;

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $USER, $DB;
require_once($CFG->libdir . '/externallib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}

// This script is used by the mobile app to check that the site is available and web services
// are allowed. In this mode, no further action is needed.
if (optional_param('appsitecheck', 0, PARAM_INT)) {
    echo json_encode((object) ['appsitecheck' => 'ok']);
    exit;
}

$username = required_param('username', PARAM_USERNAME);
$password = required_param('password', PARAM_RAW);
$serviceshortname = required_param('service', PARAM_ALPHANUMEXT);

echo $OUTPUT->header();

$username = trim(core_text::strtolower($username));
if (is_restored_user($username)) {
    throw new moodle_exception('restoredaccountresetpassword', 'webservice');
}

$systemcontext = context_system::instance();

$reason = null;

$returnedvalue = new stdClass();

$user = authenticate_user_login($username, $password, false, $reason);
if (!empty($user)) {
    require_once($CFG->dirroot . '/local/cveteval/lib.php');
    // Cannot authenticate unless maintenance access is granted.
    $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
    if (!empty($CFG->maintenance_enabled) && !$hasmaintenanceaccess) {
        throw new moodle_exception('sitemaintenance', 'admin');
    }

    if (isguestuser($user)) {
        throw new moodle_exception('noguest');
    }
    if (empty($user->confirmed)) {
        throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
    }
    // Check credential expiry.
    $userauth = get_auth_plugin($user->auth);
    if (!empty($userauth->config->expiration) && $userauth->config->expiration == 1) {
        $days2expire = $userauth->password_expire($user->username);
        if (intval($days2expire) < 0) {
            throw new moodle_exception('passwordisexpired', 'webservice');
        }
    }

    // Let enrol plugins deal with new enrolments if necessary.
    enrol_check_plugins($user);

    // Setup user session to check capability.
    manager::set_user($user);

    // Check if the service exists and is enabled.
    $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
    if (empty($service)) {
        // Will throw exception if no token found.
        throw new moodle_exception('servicenotavailable', 'webservice');
    }

    // Get an existing token or create a new one.
    $token = utils::external_generate_token_for_current_user($service);
    $privatetoken = $token->privatetoken;
    external_log_token_request($token);

    $siteadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);

    $returnedvalue->token = $token->token;
    // Private token, only transmitted to https sites and non-admin users.
    if (is_https() && !$siteadmin) {
        $returnedvalue->privatetoken = $privatetoken;
    } else {
        $returnedvalue->privatetoken = null;
    }
} else {
    switch($reason) {
        case AUTH_LOGIN_FAILED:
            $returnedvalue->errorcode = 'invalidlogin';
            break;
        case AUTH_LOGIN_NOUSER:
            $returnedvalue->errorcode = 'usernotexist';
            break;
        case AUTH_LOGIN_SUSPENDED:
            $returnedvalue->errorcode = 'usersuspended';
            break;
        case AUTH_LOGIN_LOCKOUT:
            $returnedvalue->errorcode = 'userlockedout';
            break;
        case AUTH_LOGIN_UNAUTHORISED:
            $returnedvalue->errorcode = 'userunauthorised';
            break;
        default:
            $returnedvalue->errorcode = 'unknownerror';
    }
}
echo json_encode($returnedvalue);
