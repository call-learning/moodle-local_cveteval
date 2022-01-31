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

use local_cveteval\local\datamigration\matchers\criterion;
use local_cveteval\local\datamigration\matchers\planning;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\final_evaluation\entity as final_evaluation_entity;
use local_cveteval\local\persistent\history\entity;
use local_cveteval\output\helpers\output_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * User data migration helper
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_data_migration_helper {
    /**
     * @param $contexts
     * @param $stepdata
     * @param $output
     * @return array
     * @throws \coding_exception
     */
    public static function convert_origin_appraisals($contexts, $stepdata) {
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
                    unset($newapprdata->timemodified);
                    $newapprdata->evalplanid = $evalplantargetid;
                    $newappraisal = new appraisal_entity(0, $newapprdata);
                    $newappraisal->save();
                    $evalinfo = static::get_eval_info($newapprdata->studentid, $newapprdata->appraiserid, $newapprdata->evalplanid);
                    $evalinfo->criteria = [];
                    $appraisalcrits = appraisal_criterion_entity::get_records(['appraisalid' => $appr->get('id')]);

                    foreach ($appraisalcrits as $appraisalcrit) {
                        $newappraisalcritdata = $appraisalcrit->to_record();
                        unset($newappraisalcritdata->id);
                        unset($newappraisalcritdata->timemodified);
                        $newappraisalcritdata->evalplanid = $evalplantargetid;
                        $newcriterionid = $stepdata->$context[criterion::get_entity()][$appraisalcrit->get('criterionid')] ?? 0;
                        if ($newcriterionid) {
                            $newappraisalcritdata->criterionid = $newcriterionid;
                            $newappraisal = new appraisal_entity(0, $newapprdata);
                            $newappraisal->save();
                            $evalinfo->criteria[] =
                                    static::get_eval_info($newapprdata->studentid, $newapprdata->appraiserid,
                                            $newappraisalcritdata->evalplanid, $newcriterionid, $newappraisalcritdata->grade);
                        }
                    }
                    $newappraisalinfo[] = $evalinfo;
                }
            }
        }
        return $newappraisalinfo;
    }

    /**
     * @param $contexts
     * @param $stepdata
     * @param $output
     * @return array
     */
    public static function convert_origin_finaleval($contexts, $stepdata) {
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
                foreach ($finalevals as $appr) {
                    $newfinalevaldata = $appr->to_record();
                    unset($newfinalevaldata->id);
                    unset($newfinalevaldata->timemodified);
                    $newfinalevaldata->evalplanid = $plantargetid;
                    $newfinaleval = new final_evaluation_entity(0, $newfinalevaldata);
                    $newfinaleval->save();
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
     * @param int $appraiserid
     * @param int $evalplanid
     * @param int|null $criterionid
     * @param int|null $grade
     * @return \stdClass
     * @throws \dml_exception
     */
    public static function get_eval_info($studentid, $appraiserid, $evalplanid , $criterionid = null, $grade = null) {
        $evalinfo = new \stdClass();
        $evalinfo->student = fullname(\core_user::get_user($studentid));
        $evalinfo->appraiser = fullname(\core_user::get_user($appraiserid));
        $evalinfo->planning = output_helper::export_entity_planning($evalplanid);
        if (!empty($criterionid)) {
            $evalinfo->criterion = output_helper::export_entity_criterion($criterionid);
        }
        if (!empty($grade)) {
            $evalinfo->grade = $grade;
        }
        return $evalinfo;
    }

    /**
     * Export final eval info
     *
     * @param int $studentid
     * @param int $assessorid
     * @param int $evalplanid
     * @param int $grade
     * @return \stdClass
     * @throws \dml_exception
     */
    public static function get_final_eval_info($studentid, $assessorid, $evalplanid , $grade) {
        $evalinfo = new \stdClass();
        $evalinfo->student = fullname(\core_user::get_user($studentid));
        $evalinfo->assessor = fullname(\core_user::get_user($assessorid));
        $evalinfo->planning = output_helper::export_entity_planning($evalplanid);
        $evalinfo->grade = $grade;
        return $evalinfo;
    }
}