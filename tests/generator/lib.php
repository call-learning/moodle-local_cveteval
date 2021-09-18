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
 * Generator for local_cveteval.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cveteval_generator extends \component_generator_base {
    /**
     * Create a situation entry
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_situation(array $data) {
        if (isset($data['evalgrididnumber'])) {
            $data['evalgridid'] = local_cveteval\local\persistent\evaluation_grid\entity::get_record(
                array('idnumber' => $data['evalgrididnumber']))->get('id');
            unset($data['evalgrididnumber']);
        }
        $situation = new local_cveteval\local\persistent\situation\entity(0, (object) $data);
        $situation->create();
        return $situation;
    }

    /**
     * Create a planning entry
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_evalplan(array $data) {
        if (isset($data['clsituationidnumber'])) {
            $data['clsituationid'] = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $data['clsituationidnumber']))->get('id');
            unset($data['clsituationidnumber']);
        }
        if (isset($data['groupname'])) {
            $data['groupid'] = local_cveteval\local\persistent\group\entity::get_record(
                array('name' => $data['groupname']))->get('id');
            unset($data['groupname']);
        }

        $planning = new local_cveteval\local\persistent\planning\entity(0, (object) $data);
        $planning->create();
        return $planning;
    }

    /**
     * Create eval grid
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_evaluation_grid(array $data) {
        $evalgrid = new local_cveteval\local\persistent\evaluation_grid\entity(0, (object) $data);
        $evalgrid->create();
        return $evalgrid;
    }

    /**
     * Create eval grid element
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_cevalgriditem(array $data) {
        if (isset($data['criterionidnumber'])) {
            $data['criterionid'] = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $data['criterionidnumber']))->get('id');
            unset($data['criterionidnumber']);
        }
        if (isset($data['evalgrididnumber'])) {
            $data['evalgridid'] = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $data['evalgridid']))->get('id');
            unset($data['evalgrididnumber']);
        }
        $cevalgrid = new local_cveteval\local\persistent\cevalgrid\entity(0, (object) $data);
        $cevalgrid->create();
        return $cevalgrid;
    }

    /**
     * Create a criterion in an eval grid (will create a criterion an attach it to an eval grid)
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_criterion(array $data) {
        $cevalgriddata = new stdClass();
        if (isset($data['evalgrididnumber'])) {
            $cevalgriddata->evalgridid = $data['evalgrididnumber'];
            unset($data['evalgrididnumber']);
        }
        if (!is_int($cevalgriddata->evalgridid)) {
            $cevalgriddata->evalgridid = local_cveteval\local\persistent\evaluation_grid\entity::get_record(
                array('idnumber' => $cevalgriddata->evalgridid))->get('id');
        }
        if (isset($data['parentidnumber'])) {
            $data['parentid'] = local_cveteval\local\persistent\criterion\entity::get_record(
                array('idnumber' => $data['parentidnumber']))->get('id');
            unset($data['parentidnumber']);
        }
        $criterion = new local_cveteval\local\persistent\criterion\entity(0, (object) $data);
        $criterion->create();
        $cevalgriddata = new stdClass();
        $cevalgriddata->criterionid = $criterion->get('id');
        $cevalgriddata->sort = $criterion->get('sort');
        $evalgrid = new local_cveteval\local\persistent\cevalgrid\entity(0, (object) $cevalgriddata);
        $evalgrid->create();
        return $criterion;
    }

    /**
     * Create a role
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_role(array $data) {
        $this->get_from_username($data, 'username', 'userid');
        if (isset($data['clsituationidnumber'])) {
            $data['clsituationid'] = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $data['clsituationidnumber']))->get('id');
            unset($data['clsituationidnumber']);
        }
        $role = new local_cveteval\local\persistent\role\entity(0, (object) $data);
        $role->create();
        return $role;
    }

    /**
     * Create an appraisal
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_appraisal(array $data) {
        $this->get_from_username($data, 'studentname', 'studentid');
        $this->get_from_username($data, 'appraisername', 'appraiserid');
        $evaliplanid = $this->get_evalplanid_from_date_and_situation($data);
        if ($evaliplanid) {
            $data['evalplanid'] = $evaliplanid;
        }
        $criteria = [];
        if (isset($data['criteria'])) {
            $criteria = $data['criteria'];
            unset($data['criteria']);
        }
        $appraisal = new local_cveteval\local\persistent\appraisal\entity(0, (object) $data);
        $appraisal->create();
        if (!empty($criteria)) {
            foreach ($criteria as $criterion) {
                if (!isset($criterion['appraisalid'])) {
                    $criterion['appraisalid'] = $appraisal->get('id');
                }
                $this->create_appraisal_criterion($criterion);
            }
        }
        return $appraisal;
    }

    /**
     * Create an appraisal criteria
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_appraisal_criterion(array $data) {
        if (isset($data['criterionidnumber'])) {
            $data['criterionid'] = local_cveteval\local\persistent\criterion\entity::get_record(
                array('idnumber' => $data['criterionidnumber']))->get('id');
            unset($data['criterionidnumber']);
        }
        if (!isset($data['appraisalid'])) {
            $appraisalid = $this->get_appraisalid_from_date_and_situation($data);
            if ($appraisalid) {
                $data['appraisalid'] = $appraisalid;
            }
        }
        $appraisalcrit = new local_cveteval\local\persistent\appraisal_criterion\entity(0, (object) $data);
        $appraisalcrit->create();
        return $appraisalcrit;
    }

    /**
     * Create a final evaluation
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_final_evaluation(array $data) {
        $this->get_from_username($data, 'studentname', 'studentid');
        $this->get_from_username($data, 'assessorname', 'assessorid');
        $evaliplanid = $this->get_evalplanid_from_date_and_situation($data);
        if ($evaliplanid) {
            $data['evalplanid'] = $evaliplanid;
        }
        $fevaluation = new local_cveteval\local\persistent\final_evaluation\entity(0, (object) $data);
        $fevaluation->create();
        return $fevaluation;
    }

    /**
     * Create a group assignment
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_group_assign(array $data) {
        $this->get_from_username($data, 'studentname', 'studentid');
        if (isset($data['groupname'])) {
            $data['groupid'] = local_cveteval\local\persistent\group\entity::get_record(
                array('name' => $data['groupname'])
            )->get('id');
            unset($data['groupname']);
        }
        $groupa = new local_cveteval\local\persistent\group_assignment\entity(0, (object) $data);
        $groupa->create();
        return $groupa;
    }

    /**
     * Create a group
     *
     * @param array $data
     * @throws \core\invalid_persistent_exception
     * @throws coding_exception
     */
    public function create_group(array $data) {
        $group = new local_cveteval\local\persistent\group\entity(0, (object) $data);
        $group->create();
        return $group;
    }

    /**
     * Get from username
     *
     * @param $data
     * @param $originalfield
     * @param $destfield
     * @throws dml_exception
     */
    protected function get_from_username(&$data, $originalfield, $destfield = null) {
        $destvalue = core_user::get_user_by_username($data[$originalfield])->id;
        if ($destfield) {
            $data[$destfield] = $destvalue;
        }
        unset($data[$originalfield]);
        return $destfield;
    }

    /**
     * Get evalplan from date and situation
     *
     * @param $data
     * @return int|bool
     * @throws coding_exception
     */
    protected function get_evalplanid_from_date_and_situation(&$data, $roundtime = 60): ?int {
        $evalplanid = false;
        if (isset($data['evalplandatestart']) && isset($data['evalplansituation'])) {
            $date = intval($data['evalplandatestart']);
            if (!is_int($date)) {
                $date = date_parse($data['evalplandatestart']);
                $date = mktime(
                    $date['hour'],
                    $date['minute'],
                    $date['second'],
                    $date['month'],
                    $date['day'],
                    $date['year']
                );
            }
            unset($data['evalplandatestart']);
            $situationid = local_cveteval\local\persistent\situation\entity::get_record(
                array('idnumber' => $data['evalplansituation']))->get('id');
            unset($data['evalplansituation']);
            $evalplans = local_cveteval\local\persistent\planning\entity::get_records(
                array('clsituationid' => $situationid));

            foreach ($evalplans as $ep) {
                if ($ep->get('starttime') >= ($date - $roundtime) && $ep->get('starttime') <= ($date + $roundtime)) {
                    $evalplanid = $ep->get('id');
                }
            }
        }
        return $evalplanid;
    }

    private function get_appraisalid_from_date_and_situation(&$data) {
        $evaliplanid = $this->get_evalplanid_from_date_and_situation($data);
        $appraisalid = 0;
        if ($evaliplanid) {
            if (isset($data['studentname'])) {
                $studentid = $this->get_from_username($data, 'studentname');
            } else {
                $studentid = $data['studentid'];
            }
            if (isset($data['appraisername'])) {
                $appraiserid = $this->get_from_username($data, 'appraisername');
            } else {
                $appraiserid = $data['appraiserid'];
            }
            if ($studentid && $appraiserid) {
                $appraisals = local_cveteval\local\persistent\appraisal\entity::get_records(['studentid' => $studentid,
                    'appraiserid' => $appraiserid, 'evalplanid' => $evaliplanid]);
                if ($appraisals) {
                    $appraisal = end($appraisals);
                    $appraisalid = $appraisal->get('id');
                }
            }
        }
        return $appraisalid;
    }
}
