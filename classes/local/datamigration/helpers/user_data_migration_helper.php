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

namespace local_cveteval\local\datamigration\helpers;

use coding_exception;
use core_user;
use dml_exception;
use local_cveteval\local\datamigration\matchers\criterion;
use local_cveteval\local\datamigration\matchers\planning;
use local_cveteval\local\datamigration\matchers\role;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\final_evaluation\entity as final_evaluation_entity;
use local_cveteval\local\persistent\history\entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\output\helpers\output_helper;
use stdClass;

/**
 * User data migration helper
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_data_migration_helper {
    /**
     * Convert origin appraisal
     * @param array $contexts
     * @param object $stepdata
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function convert_origin_appraisals($contexts, $stepdata) {
        global $DB;
        $newappraisalinfo = [];
        // Check first if there are any data in this context and also if there are any data to migrate
        // i.e. any appraisal, appraisalcriteria and so on. If not, not useful to bother.
        entity::disable_history();
        foreach ($contexts as $context) {
            $currentcontext = $stepdata->$context;
            $evalplanmatches = $currentcontext[planning::get_entity()];
            foreach ($evalplanmatches as $evalplanoriginid => $evalplantargetid) {
                if (!$evalplantargetid) {
                    continue; // No chosen target.
                }
                $appraisals = appraisal_entity::get_records(['evalplanid' => $evalplanoriginid]);
                foreach ($appraisals as $appr) {
                    $newapprdata = $appr->to_record();
                    unset($newapprdata->id);
                    $newapprdata->evalplanid = $evalplantargetid;
                    // TODO : if student id or assessor id have changed group and has been replaced or
                    // (for example the situation has a new appraiser and an old assessor is missing)
                    // make sure it changes also.
                    $appraiserid = self::get_current_eval_user_for_role(
                            $currentcontext,
                            $appr->get('appraiserid'),
                            $evalplanoriginid
                    );
                    if ($appraiserid != $newapprdata->appraiserid) {
                        $newapprdata->appraiserid = $appraiserid;
                    }
                    // We cannot use the entity/persistent save here. As it modify the date.
                    $newapprdata->id = $DB->insert_record(appraisal_entity::TABLE, $newapprdata);
                    $evalinfo = static::get_eval_info($newapprdata->studentid, $newapprdata->appraiserid, $newapprdata->evalplanid);
                    $evalinfo->criteria = [];
                    $appraisalcrits = appraisal_criterion_entity::get_records(['appraisalid' => $appr->get('id')]);
                    foreach ($appraisalcrits as $appraisalcrit) {
                        $newappraisalcritdata = $appraisalcrit->to_record();
                        unset($newappraisalcritdata->id);
                        $newcriterionid = $stepdata->{$context}[criterion::get_entity()][$appraisalcrit->get('criterionid')] ?? 0;
                        if ($newcriterionid) {
                            $newappraisalcritdata->criterionid = $newcriterionid;
                            $newappraisalcritdata->appraisalid = $newapprdata->id;
                            $DB->insert_record(appraisal_criterion_entity::TABLE, $newappraisalcritdata);
                            $evalinfo->criteria[] =
                                    static::get_eval_info($newapprdata->studentid, $newapprdata->appraiserid,
                                            $newapprdata->evalplanid, $newcriterionid, $newappraisalcritdata->grade);
                        }
                    }
                    $newappraisalinfo[] = $evalinfo;
                }
            }
        }
        return $newappraisalinfo;
    }

    /**
     * Get current eval for user and role
     *
     * @param object $currentcontext
     * @param int $oldappraiserid
     * @param int $evalplanoriginid
     * @return void
     * @throws coding_exception
     */
    public static function get_current_eval_user_for_role(
            $currentcontext,
            $oldappraiserid,
            $evalplanoriginid

    ) {
        $originplan = planning_entity::get_record(['id' => $evalplanoriginid]);
        $originsit = situation_entity::get_record(['id' => $originplan->get('clsituationid')]);
        $destappraiserid = $oldappraiserid;

        foreach ($currentcontext[role::get_entity()] as $originroleid => $destroleid) {
            $destrole = role_entity::get_record(['id' => $destroleid]);
            $originrolechange = role_entity::get_records(['id' => $originroleid,
                    'clsituationid' => $originsit->get('id'),
                    'userid' => $oldappraiserid,
            ]);
            if ($originrolechange) {
                foreach ($originrolechange as $originrole) {
                    if ($originrole->get('type') == $destrole->get('type')) {
                        $destappraiserid = $destrole->get('userid');
                    }
                }
            }
        }
        return $destappraiserid;
    }

    /**
     * Export final eval info
     *
     * @param int $studentid
     * @param int $appraiserid
     * @param int $evalplanid
     * @param int|null $criterionid
     * @param int|null $grade
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_eval_info($studentid, $appraiserid, $evalplanid, $criterionid = null, $grade = null) {
        $evalinfo = new stdClass();
        $evalinfo->student = fullname(core_user::get_user($studentid));
        $evalinfo->appraiser = fullname(core_user::get_user($appraiserid));
        $evalinfo->planning = output_helper::output_entity_info($evalplanid, 'planning');
        if (!empty($criterionid)) {
            $evalinfo->criterion = output_helper::output_entity_info($criterionid, 'criterion');
        }
        if (!empty($grade)) {
            $evalinfo->grade = $grade;
        }
        return $evalinfo;
    }

    /**
     * Convert origin final evaluation
     *
     * @param array $contexts
     * @param object $stepdata
     * @return array
     * @throws dml_exception
     */
    public static function convert_origin_finaleval($contexts, $stepdata) {
        global $DB;
        $newfinalevalinfo = [];
        // Check first if there are any data in this context and also if there are any data to migrate
        // i.e. any appraisal, appraisalcriteria and so on. If not, not useful to bother.
        entity::disable_history();
        foreach ($contexts as $context) {
            $currentcontext = $stepdata->$context;
            $evalplanmatches = $currentcontext[planning::get_entity()];
            foreach ($evalplanmatches as $planoriginid => $plantargetid) {
                if (!$plantargetid) {
                    continue; // No chosen target.
                }
                $finalevals = final_evaluation_entity::get_records(['evalplanid' => $planoriginid]);
                foreach ($finalevals as $finalev) {
                    $newfinalevaldata = $finalev->to_record();
                    unset($newfinalevaldata->id);
                    $newfinalevaldata->evalplanid = $plantargetid;
                    // TODO : if student id or assessor id have changed (for example the situation has a new assessor and
                    // an old assessor is missing) make sure it changes also.
                    $newfinalevaldata->assessorid = self::get_current_eval_user_for_role(
                            $currentcontext,
                            $finalev->get('assessorid'),
                            $planoriginid
                    );
                    $DB->insert_record(final_evaluation_entity::TABLE, $newfinalevaldata);
                    $newfinalevalinfo[] = static::get_final_eval_info($newfinalevaldata->studentid, $newfinalevaldata->assessorid,
                            $newfinalevaldata->evalplanid, $newfinalevaldata->grade);
                }
            }
        }
        return $newfinalevalinfo;
    }

    /**
     * Export final eval info
     *
     * @param int $studentid
     * @param int $assessorid
     * @param int $evalplanid
     * @param int $grade
     * @return stdClass
     * @throws dml_exception
     */
    public static function get_final_eval_info($studentid, $assessorid, $evalplanid, $grade) {
        $evalinfo = new stdClass();
        $evalinfo->student = fullname(core_user::get_user($studentid));
        $evalinfo->assessor = fullname(core_user::get_user($assessorid));
        $evalinfo->planning = output_helper::output_entity_info($evalplanid, 'planning');
        $evalinfo->grade = $grade;
        return $evalinfo;
    }
}
