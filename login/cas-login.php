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
 * Specific login page for CAS.
 *
 * The return URL is the URL used in the application to call the competVetEval application
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\utils;

require_once(__DIR__ . '/../../../config.php');
global $CFG, $SESSION, $USER;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/cveteval/lib.php');


if (!is_enabled_auth('cas')) {
    throw new moodle_exception('casnotenabled');
}
$authplugin = get_auth_plugin('cas');

// The auth plugin's loginpage_hook() can eventually set $frm and/or $user.
$frm = false;
$user = false;
$authplugin->loginpage_hook();
$mobilelaunchparams = [];

if ($frm and isset($frm->username)) {                             // Login WITH cookies

    $frm->username = trim(core_text::strtolower($frm->username));

    if (is_enabled_auth('none')) {
        if ($frm->username !== core_user::clean_field($frm->username, 'username')) {
            $errormsg = get_string('username') . ': ' . get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }
    if (!$user) {
        $logintoken = isset($frm->logintoken) ? $frm->logintoken : '';
        $user = authenticate_user_login($frm->username, $frm->password, false, $errorcode, $logintoken);
    }
    if ($user) {
        global $DB;
        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }

        if (empty($user->confirmed)) {// This account was never confirmed
            global $PAGE, $OUTPUT;
            $PAGE->set_title(get_string("mustconfirm"));
            $PAGE->set_heading(get_site()->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string("mustconfirm"));
            echo $OUTPUT->box(get_string("emailconfirmsent", "", s($user->email)), "generalbox boxaligncenter");
            $resendconfirmurl = new moodle_url('/login/index.php',
                [
                    'username' => $frm->username,
                    'password' => $frm->password,
                    'resendconfirmemail' => true,
                    'logintoken' => \core\session\manager::get_login_token()
                ]
            );
            echo $OUTPUT->single_button($resendconfirmurl, get_string('emailconfirmationresend'));
            echo $OUTPUT->footer();
            die;
        }

        /// Let's get them all set up.
        complete_user_login($user);

        // Get an existing token or create a new one.
        $timenow = time();
        //check if the service exists and is enabled
        $service = $DB->get_record('external_services', array('shortname' => CVETEVAL_MOBILE_SERVICE, 'enabled' => 1));
        if (empty($service)) {
            // will throw exception if no token found
            throw new moodle_exception('servicenotavailable', 'webservice');
        }

        $token = utils::external_generate_token_for_current_user($service);
        $privatetoken = $token->privatetoken;
        external_log_token_request($token);

        // Don't return the private token if the user didn't just log in and a new token wasn't created.
        if (empty($SESSION->justloggedin) and $token->timecreated < $timenow) {
            $privatetoken = null;
        }

        $siteadmin = has_capability('moodle/site:config', context_system::instance(), $USER->id);

        // Passport is generated in the mobile app, so the app opening can be validated using that variable.
        // Passports are valid only one time, it's deleted in the app once used.
        // No trailing slash.
        $siteid = md5(rtrim($CFG->wwwroot, '/'));
        $apptoken = $siteid . ':::' . $token->token;
        if ($privatetoken and is_https() and !$siteadmin) {
            $apptoken .= ':::' . $privatetoken;
        }

        $apptoken = base64_encode($apptoken);

        $mobilelaunchparams['token'] = $apptoken;
    }
}


header('Location: ' . local_cveteval\local\external\utils::get_application_launch_url($mobilelaunchparams));