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
 * Behat data generator for local_cveteval.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_cveteval_generator extends behat_generator_base {
    /**
     * Get a list of the entities that Behat can create using the generator step.
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'groups' => [
                'singular' => 'group',
                'datagenerator' => 'group',
                'required' => ['name'],
            ],
            'group assignments' => [
                'singular' => 'group_assignment',
                'datagenerator' => 'group_assignment',
                'required' => ['groupname', 'student'],
                'switchids' => ['groupname' => 'groupid', 'student' => 'studentid']
            ],
            'final evaluations' => [
                'singular' => 'final_evaluation',
                'datagenerator' => 'final_evaluation',
                'required' => ['student', 'assessor', 'grade', 'tag'],
                'switchids' => ['student' => 'studentid', 'assessor' => 'assessorid']
            ],
            'appraisals' => [
                'singular' => 'appraisal',
                'datagenerator' => 'appraisal',
                'required' => ['student', 'appraiser', 'target_plan', 'context', 'comment'],
                'switchids' => ['student' => 'studentid', 'appraiser' => 'appraiserid']
            ],
            'appraisals criterions' => [
                'singular' => 'appraisal criterion',
                'datagenerator' => 'appraisal_criterion',
                'required' => ['target_appraisal', 'target_plan', 'criterionidnumber', 'grade', 'comment'],
                'switchids' => ['criterionidnumber' => 'criterionid']
            ],
            'criteria' => [
                'singular' => 'criterion',
                'datagenerator' => 'criterion',
                'required' => ['evalgridsn', 'type', 'idnumber'],
                'switchids' => ['evalgridsn' => 'evalgridid', 'criterionparent' => 'parentid'],
            ],
            'evaluation grids' => [
                'singular' => 'evaluation_grid',
                'datagenerator' => 'evaluation_grid',
                'required' => ['name', 'idnumber'],
            ],
            'plannings' => [
                'singular' => 'plannings',
                'datagenerator' => 'planning',
                'required' => ['situationsn', 'groupname', 'starttime', 'endtime'],
                'switchids' => ['situationsn' => 'clsituationid', 'groupname' => 'groupid']
            ],
            'situations' => [
                'singular' => 'situation',
                'datagenerator' => 'situation',
                'required' => ['title', 'idnumber', 'expectedevalsnb'],
                'switchids' => ['evalgridsn' => 'evalgridid']
            ],
            'roles' => [
                'singular' => 'role',
                'datagenerator' => 'role',
                'required' => ['user', 'situationsn', 'type'],
                'switchids' => ['situationsn' => 'clsituationid', 'user' => 'userid']
            ],
            'histories' => [
                'singular' => 'history',
                'datagenerator' => 'history',
                'required' => ['idnumber', 'isactive'],
            ],
        ];
    }

    /**
     * Preprocess final_evaluation
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_final_evaluation(array $data) {
        $evaliplanid = \local_cveteval\test\test_utils::get_evalplanid_from_date_and_situation($data);
        $data['evalplanid'] = $evaliplanid;
        return $data;
    }

    /**
     * Preprocess situation
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_situation(array $data) {
        if (empty($data['evalgridsn'])) {
            $data['evalgridid'] = local_cveteval\local\persistent\evaluation_grid\entity::get_default_grid()->get('id');
        }
        if (empty($data['description'])) {
            $data['description'] = '';
        }
        return $data;
    }

    /**
     * Preprocess planning
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_planning(array $data) {
        $utc = new DateTimeZone("UTC");
        $data['starttime'] = (\DateTimeImmutable::createFromFormat("d M Y", $data['starttime'], $utc))->getTimestamp();
        $data['endtime'] = (\DateTimeImmutable::createFromFormat("d M Y", $data['endtime'], $utc))->getTimestamp();
        return $data;
    }

    /**
     * Preprocess situation
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_appraisal(array $data) {
        [$evalplandatestart, $evalplandateend, $evalplansituationsn, $evalplangroupname] = explode("/", $data['target_plan']);
        unset($data['target_plan']);
        $data['evalplanid'] = \local_cveteval\test\test_utils::get_evalplanid_from_date_and_situation($evalplandatestart,
            $evalplandateend,
            $evalplansituationsn,
            $evalplangroupname);
        return $data;
    }

    /**
     * Preprocess situation
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_appraisal_criterion(array $data) {
        [$evalplandatestart, $evalplandateend, $evalplansituationsn, $evalplangroupname] = explode("/", $data['target_plan']);
        unset($data['target_plan']);
        $evalplanid = \local_cveteval\test\test_utils::get_evalplanid_from_date_and_situation($evalplandatestart,
            $evalplandateend,
            $evalplansituationsn,
            $evalplangroupname);
        [$studentname, $appraisername, $context] = explode("/", $data['target_appraisal']);
        $data['appraisalid'] = \local_cveteval\test\test_utils::get_appraisalid_from_users_and_context($evalplanid,
            $this->get_student_id($studentname),
            $this->get_appraiser_id($appraisername),
            trim($context));
        unset($data['target_appraisal']);
        return $data;
    }

    /**
     * Gets the student id from its username.
     *
     * @param string $username
     * @return int
     * @throws Exception
     */
    protected function get_student_id($username) {
        return parent::get_user_id($username);
    }

    /**
     * Gets the appraiser id from its username.
     *
     * @param string $username
     * @return int
     * @throws Exception
     */
    protected function get_appraiser_id($username) {
        return parent::get_user_id($username);
    }

    /**
     * Preprocess role
     *
     * @param array $data Raw data.
     * @return array Processed data.
     */
    protected function preprocess_role(array $data) {
        $data['type'] = array_flip(local_cveteval\local\persistent\role\entity::ROLE_SHORTNAMES)[$data['type']] ?? 'student';
        return $data;
    }

    /**
     * Gets the group from its name
     *
     * @param string $groupname
     * @return int|null
     */
    protected function get_groupname_id($groupname) {
        $group = local_cveteval\local\persistent\group\entity::get_record(['name' => $groupname]);
        return empty($group) ? null : $group->get('id');
    }

    /**
     * Gets the criterion parent from its short name
     *
     * @param string $parentidnumber
     * @return int|null
     */
    protected function get_criterionparent_id($parentidnumber) {
        $criteria = local_cveteval\local\persistent\criterion\entity::get_record(['idnumber' => $parentidnumber]);
        return empty($criteria) ? null : $criteria->get('id');
    }

    /**
     * Gets the evalgrid from its name
     *
     * @param string $parentidnumber
     * @return int|null
     */
    protected function get_criterionidnumber_id($parentidnumber) {
        $group = local_cveteval\local\persistent\criterion\entity::get_record(['idnumber' => $parentidnumber]);
        return empty($group) ? null : $group->get('id');
    }

    /**
     * Gets the evalgrid from its short name
     *
     * @param string $evalgridsn
     * @return int|null
     */
    protected function get_evalgridsn_id($evalgridsn) {
        $evalgrid = local_cveteval\local\persistent\evaluation_grid\entity::get_record(['idnumber' => $evalgridsn]);
        return empty($evalgrid) ? null : $evalgrid->get('id');
    }

    /**
     * Gets the situation from its short name
     *
     * @param string $situationsn
     * @return int|null
     */
    protected function get_situationsn_id($situationsn) {
        $situation = local_cveteval\local\persistent\situation\entity::get_record(['idnumber' => $situationsn]);
        return empty($situation) ? null : $situation->get('id');
    }

    /**
     * Gets the assessor id from its username.
     *
     * @param string $username
     * @return int
     * @throws Exception
     */
    protected function get_assessor_id($username) {
        return parent::get_user_id($username);
    }
}
