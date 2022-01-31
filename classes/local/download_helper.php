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

namespace local_cveteval\local;

use core\dataformat;
use core_user;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid;
use local_cveteval\local\persistent\final_evaluation\entity as final_evaluation_entity;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\utils;

/**
 * Download helper
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class download_helper {
    /**
     * Generate filename
     *
     * @param string $seed
     * @return string
     */
    public static function generate_filename($seed) {
        return str_replace(' ', '_', clean_filename($seed . '-' . userdate(time())));
    }

    /**
     * Download final grades
     *
     * @param string $dataformat
     * @param string $filename
     */
    public static function download_userdata_final_evaluation(string $dataformat, $filename = null) {
        global $DB;
        $plansql = planning_entity::get_historical_sql_query('plan');
        $rs = $DB->get_recordset_sql("SELECT fe.*,situation.title AS situationtitle, 
                situation.idnumber AS situationidnumber, 
                plan.starttime AS starttime, 
                plan.endtime AS endtime,   
                grp.name AS groupname
            FROM {" . final_evaluation_entity::TABLE . "} AS fe"
                . " LEFT JOIN $plansql ON plan.id = fe.evalplanid"
                . " LEFT JOIN {" . situation_entity::TABLE . "} situation ON situation.id = plan.clsituationid "
                . " LEFT JOIN {" . group_entity::TABLE . "} grp ON grp.id = plan.groupid "
                . " WHERE plan.id IS NOT NULL");

        $fields =
                ['studentname', 'studentemail', 'studentusername', 'situation', 'planning', 'assessorname', 'assessoremail',
                        'assessorusername',
                        'grade', 'comment', 'timemodified', 'timecreated'];
        $transformcsv = function($finaleval) {
            $student = core_user::get_user($finaleval->studentid);
            $assessor = core_user::get_user($finaleval->assessorid);
            return [
                    'studentname' => utils::fast_user_fullname($finaleval->studentid),
                    'studentemail' => $student->email,
                    'studentusername' => $student->username,
                    'situation' => "$finaleval->situationtitle ({$finaleval->situationidnumber})",
                    'planning' => userdate($finaleval->starttime, get_string('strftimedate', 'core_langconfig'))
                            . '-'
                            . userdate($finaleval->endtime, get_string('strftimedate', 'core_langconfig')) .
                            "({$finaleval->groupname})",
                    'assessorname' => utils::fast_user_fullname($finaleval->assessorid),
                    'assessoremail' => $assessor->email,
                    'assessorusername' => $assessor->username,
                    'grade' => $finaleval->grade,
                    'comment' => html_to_text(format_text($finaleval->comment, $finaleval->commentformat)),
                    'timemodified' => userdate($finaleval->timemodified, get_string('strftimedatetime', 'core_langconfig')),
                    'timecreated' => userdate($finaleval->timecreated, get_string('strftimedatetime', 'core_langconfig')),
            ];
        };
        if (empty($filename)) {
            $filename = static::generate_filename('final_evaluation');
        }
        dataformat::download_data($filename, $dataformat, $fields, $rs, $transformcsv);
    }

    /**
     * Download appraisals
     *
     * @param string $dataformat
     * @param string $filename
     */
    public static function download_userdata_appraisal($importid, string $dataformat) {
        global $DB;
        $sql = planning_entity::get_historical_sql_query('plan');
        $rs = $DB->get_recordset_sql("SELECT CONCAT(a.id, ac.id) AS id, a.*, c.idnumber AS critidnumber,
                    ac.grade AS grade, ac.comment AS gradecomment, ac.commentformat AS gradecommentformat, 
                    ac.timemodified AS criteriatimemodified, ac.timecreated AS criteriatimecreated FROM {"
                . appraisal_entity::TABLE . "} AS a LEFT JOIN $sql ON plan.id = a.evalplanid"
                . " LEFT JOIN {" . appraisal_criterion_entity::TABLE . "} ac ON ac.appraisalid = a.id"
                . " LEFT JOIN {" . criterion_entity::TABLE . "} c ON c.id = ac.criterionid"
                . " WHERE plan.id IS NOT NULL");

        $fields =
                ['studentname', 'studentemail', 'studentusername', 'appraisername', 'appraiseremail', 'appraiserusername',
                        'comment', 'criterionidnumber', 'grade', 'gradecomment', 'timemodified', 'timecreated',
                        'criteriatimemodified', 'criteriatimecreated'];
        $transformcsv = function($eval) {
            $student = core_user::get_user($eval->studentid);
            $appraiser = core_user::get_user($eval->appraiserid);
            return [
                    'studentname' => utils::fast_user_fullname($eval->studentid),
                    'studentemail' => $student->email,
                    'studentusername' => $student->username,
                    'appraisername' => utils::fast_user_fullname($eval->appraiserid),
                    'appraiseremail' => $appraiser->email,
                    'appraiserusername' => $appraiser->username,
                    'comment' => html_to_text(format_text($eval->comment, $eval->commentformat)),
                    'criterionidnumber' => $eval->critidnumber,
                    'grade' => $eval->grade,
                    'gradecomment' => html_to_text(format_text($eval->gradecomment, $eval->gradecommentformat)),
                    'timemodified' => userdate($eval->timemodified, get_string('strftimedatetime', 'core_langconfig')),
                    'timecreated' => userdate($eval->timecreated, get_string('strftimedatetime', 'core_langconfig')),
                    'criteriatimemodified' => userdate($eval->criteriatimemodified,
                            get_string('strftimedatetime', 'core_langconfig')),
                    'criteriatimecreated' => userdate($eval->criteriatimecreated,
                            get_string('strftimedatetime', 'core_langconfig')),
            ];
        };
        $filename = static::generate_filename('appraisal');
        dataformat::download_data($filename, $dataformat, $fields, $rs, $transformcsv);
    }

    public static function download_model_situation($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $records = situation_entity::get_records();
        $fields =
                ['Description', 'Nom', 'Nom court', 'ResponsableUE', 'Responsable', 'Evaluateurs',
                        'Observateurs', 'Appreciations', 'GrilleEval'];
        $transformcsv = function($situation) {
            $roles = persistent\role\entity::get_records(['clsituationid' => $situation->get('id')]);
            $eval = [];
            $obs = [];
            foreach ($roles as $role) {
                $user = core_user::get_user($role->get('userid'));
                switch ($role->get('type')) {
                    case persistent\role\entity::ROLE_APPRAISER_ID:
                        $obs[] = $user->email;
                        break;
                    case persistent\role\entity::ROLE_ASSESSOR_ID:
                        $eval[] = $user->email;
                        break;
                }

            }
            $evalgrid = persistent\evaluation_grid\entity::get_record(['id' => $situation->get('evalgridid')]);
            return [
                    'Description' => $situation->get('description'),
                    'Nom' => $situation->get('title'),
                    'Nom court' => $situation->get('idnumber'),
                    'ResponsableUE' => '',
                    'Responsable' => '',
                    'Evaluateurs' => implode(',', $eval),
                    'Observateurs' => implode(',', $obs),
                    'Appreciations' => $situation->get('expectedevalsnb'),
                    'GrilleEval' => $evalgrid->get('idnumber')
            ];
        };
        $filename = static::generate_filename('situations');
        dataformat::download_data($filename, $dataformat, $fields, $records, $transformcsv);
    }

    /**
     * Download planning
     *
     * @param $importid
     * @param $dataformat
     * @return void
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function download_model_planning($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $grecords = group_entity::get_records();
        $groupidtoname = [];
        foreach ($grecords as $gr) {
            $groupidtoname[$gr->get('id')] = $gr->get('name');
        }

        $fields = array_merge(['Date début', 'Date fin'], array_values($groupidtoname));
        $precords = planning_entity::get_records();
        $flatplanning = [];

        foreach ($precords as $p) {
            $shakey = sha1($p->get('starttime') . $p->get('endtime'));
            if (empty($flatplanning[$shakey])) {
                $flatplanning[$shakey] = (object) [
                        'starttime' => userdate($p->get('starttime'), get_string('export:dateformat', 'local_cveteval')),
                        'endtime' => userdate($p->get('endtime'), get_string('export:dateformat', 'local_cveteval')),
                        'groups' => array_fill_keys($groupidtoname, '')
                ];
            }
            $sit = situation_entity::get_record(['id' => $p->get('clsituationid')]);
            $group = group_entity::get_record(['id' => $p->get('groupid')]);
            $flatplanning[$shakey]->groups[$group->get('name')] = $sit->get('idnumber');
        }

        $transformcsv = function($planning) {
            $planningdata = [
                    'Date début' => $planning->starttime,
                    'Date fin' => $planning->endtime
            ];
            foreach ($planning->groups as $groupname => $situationname) {
                $planningdata[$groupname] = $situationname;
            }
            return $planningdata;
        };
        $filename = static::generate_filename('planning');
        dataformat::download_data($filename, $dataformat, $fields, $flatplanning, $transformcsv);
    }

    /**
     * Download group
     *
     * @param $importid
     * @param $dataformat
     * @return void
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function download_model_group($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $grecords = group_entity::get_records();
        $groupidtoname = [];
        foreach ($grecords as $gr) {
            $groupidtoname[$gr->get('id')] = $gr->get('name');
        }

        $garecords = group_assignment_entity::get_records();
        $usergroups = [];
        foreach ($garecords as $ga) {
            $studentid = $ga->get('studentid');
            if (empty($usergroups[$ga->get('studentid')])) {
                $usergroups[$studentid] = (object) [
                        'studentid' => $ga->get('studentid'),
                        'groups' => []
                ];
            }
            $usergroups[$studentid]->groups[] = $groupidtoname[$ga->get('groupid')];
        }
        $fields = ["Nom de l'étudiant", "Prénom", "Identifiant"];
        $maxlength = 0;
        foreach ($usergroups as $ug) {
            $count = count($ug->groups);
            $maxlength = $count > $maxlength ? $count : $maxlength;
        }
        $groupingfields = [];
        for ($i = 0; $i < $maxlength; $i++) {
            $groupingfields[] = 'Groupement ' . ($i + 1);
        }
        $fields = array_merge($fields, $groupingfields);
        $transformcsv = function($usergroup) use ($groupingfields) {
            $user = core_user::get_user($usergroup->studentid);
            $groupassignmentdata = [
                    'Nom de l\'étudiant' => $user->lastname,
                    'Prénom' => $user->firstname,
                    'Identifiant' => $user->email,
            ];
            foreach ($usergroup->groups as $index => $groupname) {
                $groupassignmentdata[$groupingfields[$index]] = $groupname;
            }
            return $groupassignmentdata;
        };
        $filename = static::generate_filename('group');
        dataformat::download_data($filename, $dataformat, $fields, $usergroups, $transformcsv);
    }

    /**
     * Download group
     *
     * @param $importid
     * @param $dataformat
     * @return void
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function download_model_evaluation_grid($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $evaluationgrids = evaluation_grid::get_records();
        $evaluationgridsbyid = [];
        foreach ($evaluationgrids as $evalgrid) {
            $evaluationgridsbyid[$evalgrid->get('id')] = $evalgrid;
        }
        $criteria = criterion_entity::get_records();
        $criteriabyid = [];
        foreach ($criteria as $criterion) {
            $criteriabyid[$criterion->get('id')] = $criterion;
        }
        $fields = ["Evaluation Grid Id", "Criterion Id", "Criterion Parent Id", "Criterion Label"];
        $transformcsv = function($criterion) use ($evaluationgridsbyid, $criteriabyid) {
            return [
                    'Evaluation Grid Id' => $evaluationgridsbyid[$criterion->get('evalgridid')]->get('idnumber'),
                    'Criterion Id' => $criterion->get('idnumber'),
                    'Criterion Parent Id' => !empty($criterion->get('parentid')) ?
                            $criteriabyid[$criterion->get('parentid')]->get('idnumber') : '',
                    'Criterion Label' => $criterion->get('label'),
            ];
        };
        $filename = static::generate_filename('grid');
        dataformat::download_data($filename, $dataformat, $fields, $criteria, $transformcsv);
    }
}
