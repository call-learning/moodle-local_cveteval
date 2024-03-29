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

namespace local_cveteval\test;

use coding_exception;
use core_user;
use csv_import_reader;
use dml_exception;
use local_cveteval;
use local_cveteval\local\importer\importid_manager;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\utils;
use moodle_exception;
use stdClass;

/**
 * Test Utils
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_utils {

    /**
     * SHORT_SAMPLE_FILES
     */
    const SHORT_SAMPLE_FILES = [
        'users' => '/local/cveteval/tests/fixtures/ShortSample_Users.csv',
        'cveteval' => [
            'evaluation_grid' => "/Sample_Evalgrid.csv",
            'situation' => "/ShortSample_Situations.csv",
            'grouping' => "/ShortSample_Grouping.csv",
            'planning' => "/ShortSample_Planning.csv"
        ]
    ];
    /**
     * Rounding up to the time difference when looking for a specific planning
     */
    const ROUND_TIME_DIFF = 60;

    /**
     * Creates a set of test data
     *
     * TODO: Optimise queries (redondant loops)
     *
     * @param bool $cleanup
     * @param bool $verbose
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function create_random_appraisals($cleanup, $verbose = true) {
        if ($cleanup) {
            utils::cleanup_userdata(history_entity::get_current_id());
        }

        self::create_appraisals_for_students(null, 5, $verbose);
    }

    /**
     * Create appraisal for students
     *
     * @param int|null $studentid if null, all student
     * @param bool $skip
     * @param bool $verbose
     * @param int|null $forcedappraiserid
     * @param int|null $evaluationgrid
     * @throws moodle_exception
     */
    public static function create_appraisals_for_students(?int $studentid = 0,
        ?bool $skip = false,
        ?bool $verbose = true,
        ?int $forcedappraiserid = 0,
        ?int $evaluationgrid = 0) {
        global $DB;
        $studentidparam = [];
        if ($studentid) {
            $studentidparam = array('studentid' => $studentid);
        }

        $studentsga = $DB->get_records('local_cveteval_group_assign', $studentidparam);

        foreach (situation_entity::get_records() as $clsituation) {
            $clsituationid = $clsituation->get('id');
            $appraisersroles = $DB->get_records('local_cveteval_role', array('clsituationid' => $clsituationid));
            foreach ($studentsga as $studentga) {
                $evalplansid = $DB->get_fieldset_select('local_cveteval_evalplan', 'id',
                    'groupid = :groupid AND clsituationid = :clsituationid',
                    array('groupid' => $studentga->groupid, 'clsituationid' => $clsituationid));
                foreach ($evalplansid as $evalplanid) {
                    if ($skip) {
                        $shouldcreate = rand(0, 100);
                        if ($shouldcreate % $skip) {
                            continue;
                        }
                    }
                    $appraiserid = $forcedappraiserid;
                    if (!$forcedappraiserid) {
                        $appraiserindex = rand(1, count($appraisersroles)) - 1;
                        $appraiserid = array_values($appraisersroles)[$appraiserindex]->userid;
                        if (empty($appraiserid)) {
                            continue; // Appraiser not in the list of situation appraisers.
                        }
                    }
                    self::create_appraisal($studentga->studentid, $appraiserid, $evalplanid, $clsituationid,
                        $evaluationgrid, $verbose);
                }
            }
        }
    }

    /**
     * Create an appraisal
     *
     * @param int $studentid
     * @param int $appraiserid
     * @param int $evalplanid
     * @param int $situationid
     * @param int|null $evaluationgrid
     * @param bool|null $verbose
     *
     * @throws moodle_exception
     */
    public static function create_appraisal(int $studentid, int $appraiserid, int $evalplanid, int $situationid,
        ?int $evaluationgrid = 0, ?bool $verbose = false): appraisal_entity {
        global $DB;
        $clsituation = situation_entity::get_record(['id' => $situationid]);
        if (empty($evaluationgrid)) {
            $evaluationgrid = $clsituation->get('evalgridid');
            if (empty($evaluationgrid)) {
                $defaultgrid = local_cveteval\local\persistent\evaluation_grid\entity::get_default_grid();
                $evaluationgrid = $defaultgrid->get('id');
            }
        }
        $appraisal = new stdClass();
        $appraisal->studentid = $studentid;
        $appraisal->appraiserid = $appraiserid;
        $appraisal->evalplanid = $evalplanid;
        $appraisal->context = 'Context of ' . utils::fast_user_fullname($appraiserid) . "{$appraiserid}";
        $appraisal->contextformat = FORMAT_PLAIN;
        $appraisal->comment = 'Comment made by ' . utils::fast_user_fullname($appraiserid) . "{$appraiserid}";
        $appraisal->commentformat = FORMAT_PLAIN;
        $eap = new appraisal_entity(0, $appraisal);
        $eap->create();
        if ($verbose) {
            $message = 'Creating appraisal plan for ' . utils::fast_user_fullname($appraiserid) . ' in situation ' .
                $clsituation->get('title');
            if (function_exists('cli_writeln')) {
                cli_writeln($message);
            } else {
                print $message;
            }
        }
        $allcriterias = $DB->get_records_sql("SELECT crit.id as id, crit.label, crit.evalgridid
            FROM {local_cveteval_criterion} crit
            WHERE crit.evalgridid = :evalgridid",
            ['evalgridid' => $evaluationgrid]
        );
        if (empty($allcriterias)) {
            throw new moodle_exception('No criteria');
        }
        foreach ($allcriterias as $crit) {
            if ($crit->evalgridid != $clsituation->get('evalgridid')) {
                continue;
            }
            $critid = $crit->id;
            $appraisalcrit = new stdClass();
            $appraisalcrit->criterionid = $critid;
            $appraisalcrit->appraisalid = $eap->get('id');
            $appraisalcrit->grade = rand(0, 5);
            $appraisalcrit->comment =
                rand(1, 10) > 5 ? '' : 'Comment made by ' . utils::fast_user_fullname($appraiserid) . "{$appraiserid}";
            $appraisalcrit->commentformat = FORMAT_PLAIN;
            $eappraisalcrit = new appraisal_criterion_entity(0, $appraisalcrit);
            if ($verbose) {
                $message = 'Creating criteria appraisal plan for ' . utils::fast_user_fullname($appraiserid) . ' criteria ' .
                    $crit->label;
                if (function_exists('cli_writeln')) {
                    cli_writeln($message);
                } else {
                    print $message;
                }
            }
            $eappraisalcrit->create();
        }
        return $eap;
    }

    /**
     * Setup from short sample files
     *
     * @param bool $historydisabled
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function setup_from_shortsample($historydisabled = false) {
        global $CFG;
        global $DB;
        if ($historydisabled) {
            history_entity::disable_history_globally();
        }
        $transaction = $DB->start_delegated_transaction();
        $basepath = $CFG->dirroot . '/local/cveteval/tests/fixtures/';
        static::import_sample_users($CFG->dirroot . self::SHORT_SAMPLE_FILES['users']);
        $importid = static::import_sample_planning(self::SHORT_SAMPLE_FILES['cveteval'], $basepath);
        if (!$historydisabled) {
            $currenthistory = history_entity::get_record(['id' => $importid]);
            $currenthistory->set('isactive', true);
            $currenthistory->save();
        }
        $transaction->allow_commit();
    }

    /**
     * Import sample users
     *
     * @param string $samplefilepath
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function import_sample_users($samplefilepath) {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');
        require_once($CFG->dirroot . '/user/lib.php');
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');
        $content = file_get_contents($samplefilepath);
        $cir->load_csv_content($content, 'utf-8', 'semicolon');
        $cir->init();
        $columns = $cir->get_columns();
        while ($csvrow = $cir->next()) {
            $user = new stdClass();
            $user->auth = 'manual';
            $user->lang = $CFG->lang;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->confirmed = true;
            foreach ($csvrow as $key => $value) {
                $columnname = $columns[$key];
                $user->$columnname = trim($value);
            }
            if (!($existinguser = core_user::get_user_by_username($user->username))) {
                user_create_user($user, true, false);
            } else {
                $user = (object) array_merge((array) $existinguser, (array) $user);
                user_update_user($user, true, false);
                unset_user_preference('auth_forcepasswordchange', $user);
            }
        }
    }

    /**
     * Test helpers
     *
     * @param array $samplefiles
     * @param string $basepath
     * @param bool $cleanup
     *
     * @package   local_cveteval
     * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
     * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public static function import_sample_planning($samplefiles, $basepath, $cleanup = false) {
        $importidmanager = new importid_manager();
        $importid = $importidmanager->get_importid();
        foreach ($samplefiles as $type => $filename) {
            $importhelper = self::get_import_helper($type, $basepath . $filename, $importid);
            if ($cleanup) {
                $importhelper->cleanup();
            }
            if (!$importhelper->import()) {
                $errors = array_map(
                    function($record) {
                        $rec = (array) $record->to_record();
                        return array_intersect_key($rec,
                            array_flip(['messagecode', 'linenumber', 'fieldname', 'additionalinfo']));
                    },
                    $importhelper->get_processor()->get_logger()->get_logs()
                );
                throw new moodle_exception('importerror', 'local_cveteval', '', json_encode($errors));
            }
        }
        return $importid;
    }

    /**
     * Get import helper
     *
     * @param string $type
     * @param string $filename
     * @param int $importid
     * @return mixed
     * @throws moodle_exception
     */
    public static function get_import_helper($type, $filename, $importid) {
        $importclass = "\\local_cveteval\\local\\importer\\{$type}\\import_helper";
        if (!class_exists($importclass)) {
            throw new moodle_exception('importclassnotfound', 'local_cveteval', null,
                ' class:' . $importclass);
        }
        return new $importclass($filename, $importid, 'semicolon');
    }

    /**
     * Delete data from sample planning
     *
     * @param array $samplefiles
     * @param string $basepath
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function delete_sample_planning($samplefiles, $basepath) {
        $importidmanager = new importid_manager();
        $importid = $importidmanager->get_importid();
        foreach ($samplefiles as $type => $filename) {
            $importhelper = self::get_import_helper($type, $basepath . $filename, $importid);
            $importhelper->cleanup();
        }
    }

    /**
     * Delete sample users
     *
     * @param string $samplefilepath
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function delete_sample_users($samplefilepath) {
        global $CFG;
        require_once($CFG->libdir . '/csvlib.class.php');
        require_once($CFG->dirroot . '/user/lib.php');
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');
        $content = file_get_contents($samplefilepath);
        $cir->load_csv_content($content, 'utf-8', 'semicolon');
        $cir->init();
        $columns = $cir->get_columns();
        while ($csvrow = $cir->next()) {
            $sampleuser = new stdClass();
            foreach ($csvrow as $key => $value) {
                $columnname = $columns[$key];
                $sampleuser->$columnname = trim($value);
            }
            $user = core_user::get_user_by_username($sampleuser->username);
            if ($user) {
                delete_user($user);
            }
        }
    }

    /**
     * Get the relevant appraisal id frome date and situation
     *
     * @param int $evalplanid
     * @param int $studentid
     * @param int $appraiserid
     * @param string $context
     * @return int|mixed|null
     */
    public static function get_appraisalid_from_users_and_context($evalplanid,
        $studentid,
        $appraiserid,
        $context
    ) {
        global $DB;
        $appraisalid = 0;
        if ($evalplanid) {
            if ($studentid && $appraiserid) {
                $appraisals = local_cveteval\local\persistent\appraisal\entity::get_records_select(
                    "studentid = :studentid AND appraiserid = :appraiserid AND "
                    . $DB->sql_compare_text('context') . " = :context",
                    [
                        'studentid' => $studentid,
                        'appraiserid' => $appraiserid,
                        'evalplanid' => $evalplanid,
                        'context' => $context
                    ]);
                if ($appraisals) {
                    $appraisal = end($appraisals);
                    $appraisalid = $appraisal->get('id');
                }
            }
        }
        return $appraisalid;
    }

    /**
     * Get evalplan from date and situation
     *
     * @param string $evalplandatestart
     * @param string $evalplandateend
     * @param string $evalplansituationsn
     * @param string $evalplangroupname
     * @return int|null
     */
    public static function get_evalplanid_from_date_and_situation($evalplandatestart,
        $evalplandateend,
        $evalplansituationsn,
        $evalplangroupname): ?int {
        $utc = new \DateTimeZone("UTC");
        $startdate = \DateTimeImmutable::createFromFormat("d M Y", $evalplandatestart, $utc);
        $enddate = \DateTimeImmutable::createFromFormat("d M Y", $evalplandateend, $utc);
        $evalplanid = false;

        $situationid = local_cveteval\local\persistent\situation\entity::get_record(
            ['idnumber' => $evalplansituationsn])->get('id');
        $groupid = local_cveteval\local\persistent\group\entity::get_record(
            ['name' => $evalplangroupname])->get('id');

        $evalplans = local_cveteval\local\persistent\planning\entity::get_records(
            ['clsituationid' => $situationid, 'groupid' => $groupid]);

        foreach ($evalplans as $ep) {
            $planstarttimediff = $ep->get('starttime') - $startdate->getTimestamp();
            $planendtimediff = $ep->get('endtime') - $enddate->getTimestamp();
            if (abs($planstarttimediff) < self::ROUND_TIME_DIFF && abs($planendtimediff) < self::ROUND_TIME_DIFF) {
                $evalplanid = $ep->get('id');
            }
        }
        return $evalplanid;
    }

    /**
     * Get user from username
     *
     * @param string $username
     * @return int
     * @throws dml_exception
     */
    public static function get_from_username($username): int {
        return core_user::get_user_by_username($username, '*', null, MUST_EXIST)->id;
    }
}
