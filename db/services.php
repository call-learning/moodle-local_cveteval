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
 * CompetVetEval services
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'local_cveteval_get_user_type' => array(
        'classname'   => 'local_cveteval\\local\\external\\user_type',
        'methodname'  => 'get_user_type',
        'description' => 'Get user type (student, appraiser, assessor). See local_cveteval\local\role\entity',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
    'local_cveteval_get_user_situations' => array(
        'classname'   => 'local_cveteval\\local\\external\\situations',
        'methodname'  => 'get_user_situations',
        'description' => 'Get user\'s clinical situations (either to evaluate if appraiser or student own rotation).',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
    'local_cveteval_get_user_appraisals' => array(
        'classname'   => 'local_cveteval\\local\\external\\appraisals',
        'methodname'  => 'get_user_appraisals',
        'description' => 'Get user\'s clinical situations appraisals (either to evaluate if appraiser or student own rotation).',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
    'local_cveteval_set_user_appraisal' => array(
        'classname'   => 'local_cveteval\\local\\external\\appraisals',
        'methodname'  => 'set_user_appraisal',
        'description' => 'Get user\'s clinical situations appraisals (either to evaluate if appraiser or student own rotation).',
        'type'        => 'write',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
    'local_cveteval_get_criteria' => array(
        'classname'   => 'local_cveteval\\local\\external\\criteria',
        'methodname'  => 'get_criteria',
        'description' => 'Get all criteria for evaluation.',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
    'local_cveteval_get_situation_criteria' => array(
        'classname'   => 'local_cveteval\\local\\external\\situations',
        'methodname'  => 'get_situation_criteria',
        'description' => 'Get criteria attached to a given situation.',
        'type'        => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => '',
    ),
);