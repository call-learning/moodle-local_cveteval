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
use core_table\local\filter\filter;
use html_writer;
use local_cltools\local\filter\enhanced_filterset;
use local_cltools\local\filter\numeric_comparison_filter;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\roles;

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
     * @return situations
     * @throws coding_exception
     */
    public static function get_mysituations_list() {
        $entitylist = new situations();
        return $entitylist;
    }

    /**
     * Get my student list
     *
     * @param $situationid
     * @return mystudents
     * @throws coding_exception
     */
    public static function get_mystudents_list($situationid) {
        $entitylist = new mystudents(null, null, null, $situationid);
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
        // This is a special case here. Columns are deduced from the query so we need to build an empty table, then
        // the real columns when we add the filters.

        $entitylist = new appraisals_student();
        $filterset = new enhanced_filterset(
            [
                'planid' => (object)
                [
                    'filterclass' => numeric_comparison_filter::class,
                    'required' => true,
                ],
                'studentid' => (object)
                [
                    'filterclass' => numeric_comparison_filter::class,
                    'required' => true,
                ]
            ]
        );
        self::add_roles_evaluation_filterset($filterset);
        if ($evalplanid) {
            $filterset->add_filter_from_params(
                'planid', // Field name.
                filter::JOINTYPE_ALL,
                [['direction' => '=', 'value' => $evalplanid]]
            );
        }
        if ($studentid) {
            $filterset->add_filter_from_params(
                'studentid', // Field name.
                filter::JOINTYPE_ALL,
                [['direction' => '=', 'value' => $studentid]]
            );
        }
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $entitylist->set_filterset($filterset);
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
        $filterset = new enhanced_filterset(
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
            [['direction' => '=', 'value' => $appraisalid]]
        );
        $entitylist->set_filterset($filterset);
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
        $entitylist = new situations_student(null, null, null, $studentid);
        return $entitylist;
    }

    public static function add_roles_evaluation_filterset($filterset) {
        $filterset->add_filter_definition('roletype', (object)
        [
            'filterclass' => numeric_comparison_filter::class,
            'required' => true
        ]);
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ANY,
            [
                ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID],
                ['direction' => '=', 'value' => role_entity::ROLE_APPRAISER_ID]]
        );
    }

    public static function add_roles_assessor_filterset($filterset) {
        $filterset->add_filter_definition('roletype', (object)
        [
            'filterclass' => numeric_comparison_filter::class,
            'required' => true
        ]);
        $filterset->add_filter_from_params(
            'roletype', // Field name.
            filter::JOINTYPE_ALL,
            [
                ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID]
            ]
        );
    }
}
