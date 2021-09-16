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
 * Planning list for a given user
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\assessment;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use html_writer;
use local_cltools\local\filter\basic_filterset;
use local_cltools\local\filter\filter;
use local_cveteval\local\persistent\role\entity as role_entity;

/**
 * A list of function to build up the assessment pages
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assessment_utils {

    /**
     * Get my situations
     *
     * @param $userid
     * @return situations
     * @throws coding_exception
     */
    public static function get_mysituations_list($userid) {
        $uniqueid = html_writer::random_id('situationtable');
        $entitylist = new situations($uniqueid);
        $filterset = new basic_filterset(
            [
                'roletype' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
                'appraiserid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID])]
        );
        $filterset->add_filter_from_params(
            'appraiserid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $userid])]
        );
        $entitylist->set_extended_filterset($filterset);
        return $entitylist;
    }

    /**
     * Get my student list
     *
     * @param $userid
     * @param $situationid
     * @return mystudents
     * @throws coding_exception
     */
    public static function get_mystudents_list($userid, $situationid) {
        $uniqueid = html_writer::random_id('situationtable');
        $entitylist = new mystudents($uniqueid);
        $filterset = new basic_filterset(
            [
                'situationid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
                'roletype' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
                'appraiserid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'situationid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $situationid])]
        );
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID])]
        );
        $filterset->add_filter_from_params(
            'appraiserid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $userid])]
        );
        $entitylist->set_extended_filterset($filterset);
        return $entitylist;
    }

    /**
     * Get this criteria list for this evalplan / studentid
     *
     * @param $studentid
     * @param $evalplanid
     * @return appraisals_student
     * @throws coding_exception
     */
    public static function get_thissituation_list($studentid, $evalplanid) {
        $uniqueid = html_writer::random_id('thisituationtable');
        $entitylist = new appraisals_student($uniqueid);
        $filterset = new basic_filterset(
            [
                'roletype' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ],
                'planid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ],
                'studentid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ]
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID])]
        );
        $filterset->add_filter_from_params(
            'planid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $evalplanid])]
        );
        $filterset->add_filter_from_params(
            'studentid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $studentid])]
        );
        $entitylist->set_extended_filterset($filterset);
        return $entitylist;
    }

    /**
     * Get grade for this given assessment
     *
     * @param $appraisalid
     * @return appraisals_criteria
     * @throws coding_exception
     */
    public static function get_assessmentcriteria_list($appraisalid) {
        $uniqueid = html_writer::random_id('apprasailcriteriatable');
        $entitylist = new appraisals_criteria($uniqueid);
        $filterset = new basic_filterset(
            [
                'appraisalid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true
                ]
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'appraisalid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $appraisalid])]
        );
        $entitylist->set_extended_filterset($filterset);
        return $entitylist;
    }

    /**
     * Get situation for student
     *
     * @param $studentid
     * @return situations_student
     * @throws coding_exception
     */
    public static function get_situation_student($studentid) {
        $uniqueid = html_writer::random_id('allsituations');
        $entitylist = new situations_student($uniqueid);
        $filterset = new basic_filterset(
            [
                'studentid' => (object)
                [
                    'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
                    'required' => true,
                ]
            ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
            'studentid', // Field name.
            filter::JOINTYPE_ALL,
            [json_encode((object) ['direction' => '=', 'value' => $studentid])]
        );
        $entitylist->set_extended_filterset($filterset);
        return $entitylist;
    }
}