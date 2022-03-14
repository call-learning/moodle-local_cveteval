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

namespace local_cveteval;

use cache;
use cache_store;
use coding_exception;
use context_system;
use core\event\webservice_token_created;
use core\session\manager;
use core_user;
use dml_exception;
use grade_scale;
use html_writer;
use lang_string;
use local_cveteval\local\persistent\appraisal_comment\entity as appraisal_comment_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\appraisal_criterion_comment\entity as appraisal_criterion_comment_entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\model_with_history_util;
use local_cveteval\task\upload_default_criteria_grid;
use moodle_exception;
use moodle_url;
use progress_bar;
use stdClass;
use testing_util;
use tool_importer\local\exceptions\importer_exception;
use tool_importer\local\log_levels;
use tool_importer\local\logs\import_log_entity;
use webservice;

/**
 * Class utils
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {
    /**
     * Application service name
     */
    const CVETEVAL_MOBILE_SERVICE = 'cveteval_app_service';

    const DEFAULT_SCALE_ITEM = [
            0,
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10,
            11,
            12,
            13,
            14,
            15,
            16,
            17,
            18,
            19,
            20,
    ];

    /**
     * Username cache
     */
    const USER_NAME_CACHE_NAME = 'usernamecache';

    /**
     * To get usernames in a loop faster
     *
     * @param $userid
     * @return lang_string|string
     * @throws dml_exception
     */
    public static function fast_user_fullname($userid) {
        $cache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_cveteval', self::USER_NAME_CACHE_NAME);
        if (!$cache->has($userid)) {
            $cache->set($userid, fullname(core_user::get_user($userid)));
        }
        return $cache->get($userid);
    }

    /**
     * @throws coding_exception
     */
    public static function create_scale_if_not_present() {
        global $CFG;
        require_once($CFG->dirroot . '/grade/lib.php');
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
     */
    public static function create_update_default_criteria_grid() {
        if (testing_util::is_test_site()) {
            upload_default_criteria_grid::create_default_grid();
        } else {
            $task = new upload_default_criteria_grid();
            \core\task\manager::queue_adhoc_task($task, true);
        }
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
            // Similar code as in adminlib.php (admin_setting_enablemobileservice).
            set_config('enablewebservices', true);

            // Enable mobile service.
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
            // Disable web service system if no other services are enabled.
            static::get_or_create_mobile_service(); // Make sure service is created but disabled.
            $otherenabledservices = $DB->get_records_select(
                    'external_services',
                    'enabled = :enabled AND (shortname != :shortname OR shortname IS NULL)',
                    array(
                            'enabled' => 1,
                            'shortname' => self::CVETEVAL_MOBILE_SERVICE,
                    )
            );
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
     *
     * @param false $isenabled
     * @return stdClass
     * @throws coding_exception
     */
    public static function get_or_create_mobile_service($isenabled = false) {
        global $CFG;
        require_once($CFG->dirroot . '/webservice/lib.php');
        require_once($CFG->dirroot . '/local/cveteval/lib.php');

        $webservicemanager = new webservice();
        $mobileservice = $webservicemanager->get_external_service_by_shortname(self::CVETEVAL_MOBILE_SERVICE);
        if (!$mobileservice) {
            // Create it.
            // Load service info.
            require_once($CFG->dirroot . '/lib/upgradelib.php');
            external_update_descriptions('local_cveteval');
            $mobileservice = $webservicemanager->get_external_service_by_shortname(self::CVETEVAL_MOBILE_SERVICE);
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
     * @throws coding_exception
     * @throws dml_exception
     */
    private static function set_protocol_cap($status) {
        global $CFG, $DB;
        $roleid = $CFG->defaultuserroleid ?? $DB->get_field('role', 'id', array('shortname' => 'user'));
        if ($roleid) {
            $params = array();
            $params['permission'] = CAP_ALLOW;
            $params['roleid'] = $roleid;
            $params['capability'] = 'webservice/rest:use';
            $protocolcapallowed = $DB->record_exists('role_capabilities', $params);
            if ($status and !$protocolcapallowed) {
                // Need to allow the cap.
                $permission = CAP_ALLOW;
                $assign = true;
            } else if (!$status and $protocolcapallowed) {
                // Need to disallow the cap.
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
                        'shortname' => self::CVETEVAL_MOBILE_SERVICE,
                        'restrictedusers' => 0,
                        'downloadfiles' => true,
                        'uploadfiles' => false,
                        'functions' => array_keys($functions),
                ),
        );
    }

    /**
     * Get token or create token
     *
     * Very similar to the externallib.php:external_generate_token_for_current_user
     * but allowing login and tokens for the competVetEval.
     *
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
            $authoriseduser = $DB->get_record(
                    'external_services_users',
                    array('externalserviceid' => $service->id, 'userid' => $USER->id)
            );

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
                'tokentype' => EXTERNAL_TOKEN_PERMANENT,
        );
        $tokens = $DB->get_records('external_tokens', $conditions, 'timecreated ASC');

        // A bit of sanity checks.
        foreach ($tokens as $key => $token) {

            // Checks related to a specific token. (script execution continue).
            $unsettoken = false;
            // If sid is set then there must be a valid associated session no matter the token type.
            if (!empty($token->sid)) {
                if (!manager::session_exists($token->sid)) {
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
            $isofficialservice = $service->shortname == self::CVETEVAL_MOBILE_SERVICE;

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
                                'auto' => true,
                        ),
                );
                $event = webservice_token_created::create($params);
                $event->add_record_snapshot('external_tokens', $eventtoken);
                $event->trigger();
            } else {
                throw new moodle_exception('cannotcreatetoken', 'webservice', '', $service->shortname);
            }
        }

        return $token;
    }

    /**
     * Reset all data
     *
     * @throws dml_exception
     */
    public static function reset_all_data() {
        global $DB;
        foreach (array(
                'local_cveteval_evalplan',
                'local_cveteval_clsituation',
                'local_cveteval_evalgrid',
                'local_cveteval_criterion',
                'local_cveteval_role',
                'local_cveteval_appraisal',
                'local_cveteval_appr_crit',
                'local_cveteval_finalevl',
                'local_cveteval_appr_com',
                'local_cveteval_apprq_com',
                'local_cveteval_group_assign',
                'local_cveteval_group',
                'local_cveteval_history',
                'local_cveteval_history_mdl',
        ) as $table) {
            $message = "Deleting records from $table...";
            if (defined('CLI_SCRIPT') && CLI_SCRIPT == true) {
                cli_writeln($message);
            } else {
                echo html_writer::div($message);
            }
            $DB->delete_records($table);
        }
        $importlogclass = import_log_entity::class;
        $message = "Deleting records from import log...";
        if (defined('CLI_SCRIPT') && CLI_SCRIPT == true) {
            cli_writeln($message);
        } else {
            echo html_writer::div($message);
        }
        $DB->delete_records($importlogclass::TABLE, ['module' => 'local_cveteval']);
    }

    /**
     * Cleanup all models
     *
     * @param int $importid
     * @param progress_bar|null $progressbar
     */
    public static function cleanup_model($importid, $progressbar = null) {
        history_entity::set_current_id($importid, true);
        // Cleanup user data.
        self::cleanup_userdata($importid, $progressbar);
        // Cleanup model.
        foreach (model_with_history_util::get_all_entity_class_with_history() as $persistent) {
            $persistencount = $persistent::count_records();
            foreach ($persistent::get_records() as $record) {
                $table = $persistent::TABLE;
                $message = "Deleting records from {$table}...";
                $currentcount++;
                if (defined('CLI_SCRIPT') && CLI_SCRIPT == true) {
                    cli_writeln($message);
                } else {
                    if ($progressbar) {
                        $progressbar->update($currentcount++, $persistencount, $persistent::TABLE);
                    }
                }
                $record->delete();
            }
            $currentcount = 0;
        }
        history_entity::reset_current_id();
        $history = new history_entity($importid);
        $history->delete();
        // Cleanup logs.
        foreach (local\persistent\import_log\entity::get_records(['importid' => $importid]) as $ilog) {
            $ilog->delete();
        }

    }

    /**
     * Cleanup all user data
     *
     * @param int $importid
     * @param progress_bar|null $progressbar
     * @return void
     */
    public static function cleanup_userdata($importid, $progressbar = null) {
        history_entity::set_current_id($importid, true);
        foreach (local\persistent\planning\entity::get_records() as $evalplan) {
            $finalevals = local\persistent\final_evaluation\entity::get_records(['evalplanid' => $evalplan->get('id')]);
            $totalcount = count($finalevals);
            $currentcount = 0;
            foreach ($finalevals as $finaleval) {
                $progressbar->update($currentcount++, $totalcount,
                        get_string('final_evaluation:entity', 'local_cveteval') . " - " . $finaleval->get('id'));
                $finaleval->delete();
            }
            $appraisals = local\persistent\appraisal\entity::get_records(['evalplanid' => $evalplan->get('id')]);
            $totalcount = count($appraisals);
            $currentcount = 0;
            foreach ($appraisals as $appraisal) {
                if ($progressbar) {
                    $progressbar->update($currentcount++, $totalcount,
                            get_string('appraisal:entity', 'local_cveteval') . " - " . $appraisal->get('id') . " / " .
                            $appraisal->get('id'));
                }
                foreach (appraisal_criterion_entity::get_records(['appraisalid' => $appraisal->get('id')]) as $appraisalcriterion) {
                    foreach (appraisal_criterion_comment_entity::get_records([
                                    'appraisalqtemplateid' => $appraisalcriterion->get('id')
                            ]
                    ) as $appraisalcriterioncomment) {
                        $appraisalcriterioncomment->delete();
                    }
                    $appraisalcriterion->delete();
                }
                foreach (appraisal_comment_entity::get_records(['appraisalid' => $appraisal->get('id')]) as $appraisalcomment) {
                    $appraisalcomment->delete();
                }
                $appraisal->delete();
            }
        }
    }

    /**
     * Create initial history after upgrade
     */
    public static function migrate_current_entity_to_history() {
        $idnumber = get_string('defaulthistoryidnumber', 'local_cveteval', userdate(time(), get_string('strftimedatetimeshort')));

        $history = new local\persistent\history\entity(
                0,
                (object) ['idnumber' => $idnumber, 'comments' => "Initial upgrade", 'isactive' => true]
        );
        $history->create();
        $historyclasstomigrate = [
                local\persistent\criterion\entity::class,
                local\persistent\evaluation_grid\entity::class,
                local\persistent\group\entity::class,
                local\persistent\group_assignment\entity::class,
                local\persistent\planning\entity::class,
                local\persistent\role\entity::class,
                local\persistent\situation\entity::class,
        ];
        local\persistent\history\entity::disable_history();
        foreach ($historyclasstomigrate as $entityclass) {
            foreach ($entityclass::get_records() as $record) {
                $historymodel = new history_entity(
                        0, (object) [
                        'tablename' => $entityclass::TABLE,
                        'tableid' => $record->get('id'),
                        'historyid' => $history->get('id')
                        ]
                );
                $historymodel->create();
            }
        }
        local\persistent\history\entity::reset_current_id();
    }

    public static function check_user_exists_or_multiple($email, $rowindex, $messagemultiple, $messagenotfound, $fieldname) {
        try {
            core_user::get_user_by_email($email, '*', null, MUST_EXIST);
        } catch (moodle_exception $e) {
            $message = core_user::get_user_by_email($email) ? $messagemultiple : $messagenotfound;
            throw new importer_exception(
                    $message,
                    $rowindex,
                    $fieldname,
                    'local_cveteval',
                    $email,
                    log_levels::LEVEL_ERROR
            );
        }
    }

    /**
     * Setup page navigation for entity managaement
     *
     * @param int $importid
     * @return void
     * @throws coding_exception
     */
    public static function setup_entity_management_page_navigation($importid) {
        global $PAGE;
        $PAGE->navbar->add(
                get_string('import:list', 'local_cveteval'),
                new moodle_url('/local/cveteval/admin/importindex.php'));
        $import = new import_log_entity($importid);
        $PAGE->navbar->add(
                $import->get('idnumber'),
                new moodle_url('/local/cveteval/manage/index.php', ['importid' => $importid]));

    }
}
