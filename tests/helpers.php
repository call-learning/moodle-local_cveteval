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
use core\invalid_persistent_exception;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\local\utils;

defined('MOODLE_INTERNAL') || die();
/**
 * Test helpers
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function import_sample_planning($samplefiles, $cleanup = false) {
    $importid = utils::get_next_importid();
    foreach ($samplefiles as $type => $filename) {
        $importclass = "\\local_cveteval\\local\\importer\\{$type}\\import_helper";
        if (!class_exists($importclass)) {
            throw new moodle_exception('importclassnotfound', 'local_cveteval', null,
                ' class:' . $importclass);
        }
        $importhelper = new $importclass($filename, $importid, 'semicolon');

        if ($cleanup) {
            $importhelper->cleanup();
        }
        $importhelper->import();
    }
}

/**
 * Import sample users
 *
 * @throws dml_exception
 * @throws moodle_exception
 */
function import_sample_users($samplefilepath) {
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
 * Creates a set of test data
 *
 * TODO: Optimise queries (redondant loops)
 *
 * @throws invalid_persistent_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function create_random_appraisals($cleanup, $verbose = true) {
    global $DB;

    if ($cleanup) {
        $DB->delete_records('local_cveteval_appraisal');
        $DB->delete_records('local_cveteval_appr_crit');
    }

    create_appraisal_for_students(null, 5, true);
}

/**
 * @param $allcriterias
 * @param null $studentid if null, all student
 * @param null $skip
 * @throws invalid_persistent_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function create_appraisal_for_students($studentid = null, $skip = null, $verbose = true, $forcedappraiserid = 0) {
    global $DB;
    $studentidparam = [];
    if ($studentid) {
        $studentidparam = array('studentid' => $studentid);
    }
    $allcriterias = $DB->get_records_sql("SELECT crit.id as id, crit.label, egrid.evalgridid
            FROM {local_cveteval_criterion} crit
            LEFT JOIN {local_cveteval_cevalgrid} egrid ON crit.id = egrid.criterionid"
    );
    $studentsga = $DB->get_records('local_cveteval_group_assign', $studentidparam);

    foreach (situation_entity::get_records() as $clsituation) {
        $appraisersroles = $DB->get_records('local_cveteval_role', array('clsituationid' => $clsituation->get('id')));
        foreach ($studentsga as $studentga) {
            $evalplansid = $DB->get_fieldset_select('local_cveteval_evalplan', 'id',
                'groupid = :groupid AND clsituationid = :clsituationid',
                array('groupid' => $studentga->groupid, 'clsituationid' => $clsituation->get('id')));
            foreach ($evalplansid as $evalplanid) {
                if ($skip) {
                    $shouldcreate = rand(0, 100);
                    if ($shouldcreate % $skip) {
                        continue;
                    }
                }
                $appid = $forcedappraiserid;
                if (!$forcedappraiserid) {
                    $appraiserindex = rand(1, count($appraisersroles)) - 1;
                    $appid = array_values($appraisersroles)[$appraiserindex]->userid;
                }
                $appraisal = new stdClass();
                $appraisal->studentid = $studentga->studentid;
                $appraisal->appraiserid = $appid;
                $appraisal->evalplanid = $evalplanid;
                $appraisal->context = 'Context of ' . utils::fast_user_fullname($appid) . "{$appid}";
                $appraisal->contextformat = FORMAT_PLAIN;
                $appraisal->comment = 'Comment made by ' . utils::fast_user_fullname($appid) . "{$appid}";
                $appraisal->commentformat = FORMAT_PLAIN;
                $eap = new appraisal_entity(0, $appraisal);
                $eap->create();
                if ($verbose) {
                    cli_writeln('Creating appraisal plan for ' . utils::fast_user_fullname($appid) . ' in situation ' .
                        $clsituation->get('title'));
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
                        rand(1, 10) > 5 ? '' : 'Comment made by ' . utils::fast_user_fullname($appid) . "{$appid}";
                    $appraisalcrit->commentformat = FORMAT_PLAIN;
                    $eappraisalcrit = new appraisal_criterion_entity(0, $appraisalcrit);
                    if ($verbose) {
                        cli_writeln('Creating criteria appraisal plan for ' . utils::fast_user_fullname($appid) . ' criteria ' .
                            $crit->label);
                    }
                    $eappraisalcrit->create();
                }
            }
        }
    }
}
