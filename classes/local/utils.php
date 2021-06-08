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
 * Various utilities
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local;

use coding_exception;
use context_system;
use core_user;
use dml_exception;
use grade_scale;
use lang_string;
use local_cveteval\local\persistent\role\entity as role_entity;
use moodle_exception;
use stdClass;
use tool_importer\local\import_log;

defined('MOODLE_INTERNAL') || die();

class utils {
    const DEFAULT_SCALE_ITEM = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
    ];

    /**
     * To get usernames in a loop faster
     *
     * @param $userid
     * @return lang_string|string
     * @throws dml_exception
     */
    public static function fast_user_fullname($userid) {
        static $usernames = [];
        if (empty($usernames[$userid])) {
            $usernames[$userid] = fullname(core_user::get_user($userid));
        }
        return $usernames[$userid];
    }

    /**
     * Get role identifier
     *
     * If both appraiser and assessor, assessor role will be returned
     *
     * @param $userid
     * @return int
     * @throws dml_exception
     */
    public static function get_user_role_id($userid) {
        $roleid = role_entity::ROLE_STUDENT_ID;
        // Check that user exists first, if not it will be a student role.
        if ($user = core_user::get_user($userid)) {
            $roles = role_entity::get_records_select(
                "userid = :userid AND type IN (:appraisertype, :assessortype)",
                array(
                    'userid' => $userid,
                    'appraisertype' => role_entity::ROLE_APPRAISER_ID,
                    'assessortype' => role_entity::ROLE_ASSESSOR_ID,
                ));
            $isappraiser = false;
            $isassessor = false;
            foreach ($roles as $role) {
                if ($role->get('type') == role_entity::ROLE_APPRAISER_ID) {
                    $isappraiser = true;
                }
                if ($role->get('type') == role_entity::ROLE_ASSESSOR_ID) {
                    $isassessor = true;
                }
            }
            if (persistent\group_assignment\entity::record_exists_select('studentid =:sid', array('sid' => $userid))) {
                // As we check this globally and not per situation, this means that we ensure that a student stays a student.
                $isappraiser = false;
                $isassessor = false;
            }
            if ($isappraiser) {
                $roleid = role_entity::ROLE_APPRAISER_ID;
            }
            if ($isassessor) {
                $roleid = role_entity::ROLE_ASSESSOR_ID;
            }
        }

        return $roleid;
    }

    /**
     * @throws coding_exception
     */
    public static function create_scale_if_not_present() {
        global $CFG;
        require_once($CFG->dirroot.'/grade/lib.php');
        $scales = grade_scale::fetch_all_global();
        $defaultscale = null;
        foreach ($scales as $scale) {
            if ($scale->get_name() == get_string('grade:defaultscale', 'local_cveteval')) {
                $defaultscale = $scale;
            }
        }
        if (empty($defaultscale)) {
            global $USER;
            $scalerecord = new stdClass();
            $scalerecord->standard = 1;
            $scalerecord->courseid = 0;
            $scalerecord->scale = join(',', self::DEFAULT_SCALE_ITEM);
            $scalerecord->userid = $USER->id;
            $scalerecord->name = get_string('grade:defaultscale', 'local_cveteval');
            $scalerecord->description = get_string('grade:defaultscale:description', 'local_cveteval');
            $scalerecord->descriptionformat = FORMAT_PLAIN;
            $defaultscale = new grade_scale($scalerecord);
            $defaultscale->insert();
        } else {
            $defaultscale->load_items(self::DEFAULT_SCALE_ITEM);
            $defaultscale->update();
        }
    }

    /**
     * @return false|int|mixed
     * @return int
     * @throws dml_exception
     */
    public static function get_next_importid() {
        global $DB;
        $table = import_log::TABLE;
        // Get the Max importid.
        // We assume here that there is at least a log from the module with the module set to 'local_cveteval'.
        $maximportid = $DB->get_field_sql('SELECT COALESCE(MAX(importid),0) AS maximportid FROM {'
            . $table . '} WHERE module=:module', ['module' => 'local_cveteval']);
        return empty($maximportid) ? 0 : $maximportid + 1;
    }

    /**
     * Enable or disable mobile service and associated capabilities
     *
     * @param $enabled
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function setup_mobile_service($enabled) {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/local/cveteval/lib.php');
        // Same routine as when we enable MOBILE Services.

        global $DB;
        if ($enabled) {
            // Similar code as in adminlib.php (admin_setting_enablemobileservice)
            set_config('enablewebservices', true);

            //Enable mobile service
            static::get_or_create_mobile_service(true);

            // Enable REST server.
            $activeprotocols = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);

            $updateprotocol = false;
            if (!in_array('rest', $activeprotocols)) {
                $activeprotocols[] = 'rest';
                $updateprotocol = true;
            }

            if ($updateprotocol) {
                set_config('webserviceprotocols', implode(',', $activeprotocols));
            }

            // Allow rest:use capability for authenticated user.
            static::set_protocol_cap(true);
        } else {
            //disable web service system if no other services are enabled
            static::get_or_create_mobile_service(); // Make sure service is created but disabled.
            $otherenabledservices = $DB->get_records_select('external_services',
                'enabled = :enabled AND (shortname != :shortname OR shortname IS NULL)', array('enabled' => 1,
                    'shortname' => CVETEVAL_MOBILE_SERVICE));
            if (empty($otherenabledservices)) {
                set_config('enablewebservices', false);

                // Also disable REST server.
                $activeprotocols = empty($CFG->webserviceprotocols) ? array() : explode(',', $CFG->webserviceprotocols);

                $protocolkey = array_search('rest', $activeprotocols);
                if ($protocolkey !== false) {
                    unset($activeprotocols[$protocolkey]);
                    $updateprotocol = true;
                }

                if ($updateprotocol) {
                    set_config('webserviceprotocols', implode(',', $activeprotocols));
                }

                // Disallow rest:use capability for authenticated user.
                static::set_protocol_cap(false);
            }

        }
        require_once($CFG->dirroot . '/lib/upgradelib.php');
        external_update_descriptions('local_cveteval');
    }

    /**
     * Get or create mobile service
     * @param false $isenabled
     * @return stdClass
     * @throws coding_exception
     */
    public static function get_or_create_mobile_service($isenabled = false) {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/local/cveteval/lib.php');

        $webservicemanager = new \webservice();
        $mobileservice = $webservicemanager->get_external_service_by_shortname(CVETEVAL_MOBILE_SERVICE);
        if (!$mobileservice) {
            // Create it.
            // Load service info
            require_once($CFG->dirroot . '/lib/upgradelib.php');
            external_update_descriptions('local_cveteval');
            $mobileservice = $webservicemanager->get_external_service_by_shortname(CVETEVAL_MOBILE_SERVICE);
        }
        $mobileservice->enabled = $isenabled;
        $webservicemanager->update_external_service($mobileservice);
        return $mobileservice;
    }

    /**
     * This is a replica of the admin settings for mobile application
     *
     * Set the 'webservice/rest:use' to the Authenticated user role (allow or not)
     *
     * @param bool $status true to allow, false to not set
     */
    private static function set_protocol_cap($status) {
        global $CFG, $DB;
        $roleid = $CFG->defaultuserroleid ?? $DB->get_field('role', array('shortname' => 'user'));
        if ($roleid) {
            $params = array();
            $params['permission'] = CAP_ALLOW;
            $params['roleid'] = $roleid;
            $params['capability'] = 'webservice/rest:use';
            $protocolcapallowed = $DB->record_exists('role_capabilities', $params);
            if ($status and !$protocolcapallowed) {
                //need to allow the cap
                $permission = CAP_ALLOW;
                $assign = true;
            } else if (!$status and $protocolcapallowed) {
                //need to disallow the cap
                $permission = CAP_INHERIT;
                $assign = true;
            }
            if (!empty($assign)) {
                $systemcontext = context_system::instance();
                assign_capability('webservice/rest:use', $permission, $roleid, $systemcontext->id, true);
            }
        }
    }

    /**
     *
     * @param array $functions
     * @return array[]
     * @throws coding_exception
     */
    public static function get_mobile_services_definition($functions) {
        $cvemobilename = get_string('cvetevalappservicename', 'local_cveteval');
        return array(
            $cvemobilename => array(
                'enabled' => 0,
                'requiredcapability' => 'local/cveteval:mobileaccess',
                'component' => 'local_cveteval',
                'shortname' => CVETEVAL_MOBILE_SERVICE,
                'restrictedusers' => 0,
                'downloadfiles' => true,
                'uploadfiles' => false,
                'functions' => array_keys($functions)
            )
        );
    }

    /**
     * Get token or create token
     *
     * Very similar to the externallib.php:external_generate_token_for_current_user
     * but allowing login and tokens for the competVetEval.
     * @param object $service
     * @return mixed|stdClass|null
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function external_generate_token_for_current_user($service) {
        global $DB, $USER, $CFG;

        core_user::require_active_user($USER, true, true);

        // Check if there is any required system capability.
        if ($service->requiredcapability and !has_capability($service->requiredcapability, context_system::instance())) {
            throw new moodle_exception('missingrequiredcapability', 'webservice', '', $service->requiredcapability);
        }

        // Specific checks related to user restricted service.
        if ($service->restrictedusers) {
            $authoriseduser = $DB->get_record('external_services_users',
                array('externalserviceid' => $service->id, 'userid' => $USER->id));

            if (empty($authoriseduser)) {
                throw new moodle_exception('usernotallowed', 'webservice', '', $service->shortname);
            }

            if (!empty($authoriseduser->validuntil) and $authoriseduser->validuntil < time()) {
                throw new moodle_exception('invalidtimedtoken', 'webservice');
            }

            if (!empty($authoriseduser->iprestriction) and !address_in_subnet(getremoteaddr(), $authoriseduser->iprestriction)) {
                throw new moodle_exception('invalidiptoken', 'webservice');
            }
        }

        // Check if a token has already been created for this user and this service.
        $conditions = array(
            'userid' => $USER->id,
            'externalserviceid' => $service->id,
            'tokentype' => EXTERNAL_TOKEN_PERMANENT
        );
        $tokens = $DB->get_records('external_tokens', $conditions, 'timecreated ASC');

        // A bit of sanity checks.
        foreach ($tokens as $key => $token) {

            // Checks related to a specific token. (script execution continue).
            $unsettoken = false;
            // If sid is set then there must be a valid associated session no matter the token type.
            if (!empty($token->sid)) {
                if (!\core\session\manager::session_exists($token->sid)) {
                    // This token will never be valid anymore, delete it.
                    $DB->delete_records('external_tokens', array('sid' => $token->sid));
                    $unsettoken = true;
                }
            }

            // Remove token is not valid anymore.
            if (!empty($token->validuntil) and $token->validuntil < time()) {
                $DB->delete_records('external_tokens', array('token' => $token->token, 'tokentype' => EXTERNAL_TOKEN_PERMANENT));
                $unsettoken = true;
            }

            // Remove token if its ip not in whitelist.
            if (isset($token->iprestriction) and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
                $unsettoken = true;
            }

            if ($unsettoken) {
                unset($tokens[$key]);
            }
        }

        // If some valid tokens exist then use the most recent.
        if (count($tokens) > 0) {
            $token = array_pop($tokens);
        } else {
            $context = context_system::instance();
            $isofficialservice = $service->shortname == CVETEVAL_MOBILE_SERVICE;

            if (($isofficialservice and has_capability('moodle/webservice:createmobiletoken', $context)) or
                (!is_siteadmin($USER) && has_capability('moodle/webservice:createtoken', $context))) {

                // Create a new token.
                $token = new stdClass;
                $token->token = md5(uniqid(rand(), 1));
                $token->userid = $USER->id;
                $token->tokentype = EXTERNAL_TOKEN_PERMANENT;
                $token->contextid = context_system::instance()->id;
                $token->creatorid = $USER->id;
                $token->timecreated = time();
                $token->externalserviceid = $service->id;
                // By default tokens are valid for 12 weeks.
                $token->validuntil = $token->timecreated + $CFG->tokenduration;
                $token->iprestriction = null;
                $token->sid = null;
                $token->lastaccess = null;
                // Generate the private token, it must be transmitted only via https.
                $token->privatetoken = random_string(64);
                $token->id = $DB->insert_record('external_tokens', $token);

                $eventtoken = clone $token;
                $eventtoken->privatetoken = null;
                $params = array(
                    'objectid' => $eventtoken->id,
                    'relateduserid' => $USER->id,
                    'other' => array(
                        'auto' => true
                    )
                );
                $event = \core\event\webservice_token_created::create($params);
                $event->add_record_snapshot('external_tokens', $eventtoken);
                $event->trigger();
            } else {
                throw new moodle_exception('cannotcreatetoken', 'webservice', '', $service->shortname);
            }
        }
        return $token;
    }

}
