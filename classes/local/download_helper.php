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

use coding_exception;
use core\dataformat;
use core_user;
use gradereport_singleview\local\ui\empty_element;
use local_cveteval\local\persistent\appraisal\entity as appraisal_entity;
use local_cveteval\local\persistent\appraisal_criterion\entity as appraisal_criterion_entity;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid;
use local_cveteval\local\persistent\final_evaluation\entity as final_evaluation_entity;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\utils;
use moodle_exception;

/**
 * Download helper
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class download_helper {
    /**
     * Download final grades
     *
     * @param int $importid
     * @param string $dataformat
     * @param string $filename
     */
    public static function download_userdata_final_evaluation($importid, string $dataformat, $filename = null) {
        global $DB;
        if ($importid) {
            history_entity::set_current_id($importid);
        }
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
                ['studentname', 'studentemail', 'studentusername', 'situation', 'planning', 'group', 'assessorname',
                        'assessoremail',
                        'assessorusername',
                        'grade', 'comment', 'timemodified', 'timecreated'];
        $transformcsv = function($finaleval) {
            $studentinfo = static::get_user_info($finaleval->studentid);
            $assessorinfo = static::get_user_info($finaleval->assessorid);
            return [
                    'studentname' => $studentinfo->fullname,
                    'studentemail' => $studentinfo->email,
                    'studentusername' => $studentinfo->username,
                    'situation' => "$finaleval->situationtitle ({$finaleval->situationidnumber})",
                    'planning' => userdate($finaleval->starttime, get_string('strftimedate', 'core_langconfig'))
                            . '-'
                            . userdate($finaleval->endtime, get_string('strftimedate', 'core_langconfig')),
                    'group' => $finaleval->groupname,
                    'assessorname' => $assessorinfo->fullname,
                    'assessoremail' => $assessorinfo->email,
                    'assessorusername' => $assessorinfo->username,
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
     * Get user info
     *
     * @param int $userid
     * @return object
     */
    protected static function get_user_info($userid) {
        if ($userid) {
            $user = core_user::get_user($userid);
            return (object) [
                    'fullname' => utils::fast_user_fullname($user->id),
                    'email' => $user->email,
                    'username' => $user->username
            ];
        } else {
            return (object) [
                    'fullname' => get_string('evaluation:waiting', 'local_cveteval'),
                    'email' => '',
                    'username' => ''
            ];
        }

    }

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
     * Download appraisals
     *
     * @param int $importid
     * @param string $dataformat
     * @throws \dml_exception
     * @throws coding_exception
     */
    public static function download_userdata_appraisal($importid, string $dataformat) {
        global $DB;
        persistent\history\entity::set_current_id($importid);
        $sql = planning_entity::get_historical_sql_query('plan');
        $rs = $DB->get_recordset_sql("SELECT CONCAT(a.id, ac.id) AS id, a.*, c.idnumber AS critidnumber,
                    ac.grade AS grade, ac.comment AS gradecomment, ac.commentformat AS gradecommentformat,
                    ac.timemodified AS criteriatimemodified, ac.timecreated AS criteriatimecreated,
                    s.idnumber AS situationlabel,
                    plan.starttime AS starttime,
                    plan.endtime AS endtime
                FROM {"
                . appraisal_entity::TABLE . "} AS a LEFT JOIN $sql ON plan.id = a.evalplanid"
                . " LEFT JOIN {" . appraisal_criterion_entity::TABLE . "} ac ON ac.appraisalid = a.id"
                . " LEFT JOIN {" . criterion_entity::TABLE . "} c ON c.id = ac.criterionid"
                . " LEFT JOIN {" . situation_entity::TABLE . "} s ON s.id = plan.clsituationid"
                . " WHERE plan.id IS NOT NULL");

        $fields =
                ['situation', 'planning', 'studentname', 'studentemail', 'studentusername', 'appraisername', 'appraiseremail',
                        'appraiserusername',
                        'comment', 'criterionidnumber', 'grade', 'gradecomment', 'timemodified', 'timecreated',
                        'criteriatimemodified', 'criteriatimecreated'];
        $transformcsv = function($eval) {
            $studentinfo = static::get_user_info($eval->studentid);
            $appraiserinfo = static::get_user_info($eval->appraiserid);
            return [
                    'situation' => "$eval->situationlabel",
                    'planning' => userdate($eval->starttime, get_string('strftimedate', 'core_langconfig'))
                            . '/'
                            . userdate($eval->endtime, get_string('strftimedate', 'core_langconfig')),
                    'studentname' => $studentinfo->fullname,
                    'studentemail' => $studentinfo->email,
                    'studentusername' => $studentinfo->username,
                    'appraisername' => $appraiserinfo->fullname,
                    'appraiseremail' => $appraiserinfo->email,
                    'appraiserusername' => $appraiserinfo->username,
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

    /**
     * Download model situation
     *
     * @param int $importid
     * @param string $dataformat
     * @return void
     */
    public static function download_model_situation($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $records = situation_entity::get_records();
        $fields =
                ['Description', 'Nom', 'Nom court', 'Responsable', 'Evaluateurs',
                        'Observateurs', 'Appreciations', 'GrilleEval'];
        $transformcsv = function($situation) {
            $roles = persistent\role\entity::get_records(['clsituationid' => $situation->get('id')]);
            $eval = [];
            $obs = [];
            foreach ($roles as $role) {
                $userinfo = static::get_user_info($role->get('userid'));
                switch ($role->get('type')) {
                    case persistent\role\entity::ROLE_APPRAISER_ID:
                        $obs[] = $userinfo->email;
                        break;
                    case persistent\role\entity::ROLE_ASSESSOR_ID:
                        $eval[] = $userinfo->email;
                        break;
                }
            }
            $evalgrid = persistent\evaluation_grid\entity::get_record(['id' => $situation->get('evalgridid')]);
            return [
                    'Description' => $situation->get('description'),
                    'Nom' => $situation->get('title'),
                    'Nom court' => $situation->get('idnumber'),
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
     * @param int $importid
     * @param string $dataformat
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function download_model_planning($importid, $dataformat) {
        persistent\history\entity::set_current_id($importid);
        $grecords = group_entity::get_records();
        $groupidtoname = [];
        foreach ($grecords as $gr) {
            $groupidtoname[$gr->get('id')] = $gr->get('name');
        }
        // Then sort.
        uasort($groupidtoname, function($g1, $g2) {
            if ($g1 == $g2) {
                return 0;
            }
            return ($g1 < $g2) ? -1 : 1;
        });

        $fields = array_merge(['Date début', 'Date fin'], array_values($groupidtoname));
        $precords = planning_entity::get_records();
        $flatplanning = [];

        foreach ($precords as $p) {
            $shakey = $p->get('starttime') . $p->get('endtime');
            $sit = situation_entity::get_record(['id' => $p->get('clsituationid')]);
            $group = group_entity::get_record(['id' => $p->get('groupid')]);
            while (!empty($flatplanning[$shakey]->groups[$group->get('name')])) {
                $shakey .= '1';
            }
            if (empty($flatplanning[$shakey])) {
                $flatplanning[$shakey] = (object) [
                    'starttime' => $p->get('starttime'),
                    'endtime' => $p->get('endtime'),
                    'groups' => array_fill_keys($groupidtoname, '')
                ];
            }
            $flatplanning[$shakey]->groups[$group->get('name')] = $sit->get('idnumber');
        }
        // Then sort.
        uasort($flatplanning, function($p1, $p2) {
            if ($p1->starttime == $p2->starttime) {
                return 0;
            }
            return ($p1->starttime < $p2->starttime) ? -1 : 1;
        });

        $transformcsv = function($planning) {
            $planningdata = [
                'Date début' => userdate($planning->starttime, get_string('export:dateformat', 'local_cveteval')),
                'Date fin' => userdate($planning->endtime, get_string('export:dateformat', 'local_cveteval')),
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
     * @param int $importid
     * @param string $dataformat
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
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
            $maxlength = max($count, $maxlength);
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
     * @param int $importid
     * @param string $dataformat
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
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
