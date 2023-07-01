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

use local_cveteval\utils;

defined('MOODLE_INTERNAL') || die();
$functions = array(
    'local_cveteval_get_user_type' => array(
        'classname' => 'local_cveteval\\local\\external\\user_type',
        'methodname' => 'execute',
        'description' => 'Get user type (student, appraiser, assessor). See local_cveteval\local\role\entity',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_user_profile' => array(
        'classname' => 'local_cveteval\\local\\external\\user_profile',
        'methodname' => 'execute',
        'description' => 'Get user profile information',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        // TODO: We will need to create new service for this app.
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_appraisal' => array(
        'classname' => 'local_cveteval\\local\\external\\appraisal',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_appr_crit' => array(
        'classname' => 'local_cveteval\\local\\external\\appr_crit',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_evalplan' => array(
        'classname' => 'local_cveteval\\local\\external\\evalplan',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_clsituation' => array(
        'classname' => 'local_cveteval\\local\\external\\clsituation',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_criterion' => array(
        'classname' => 'local_cveteval\\local\\external\\criterion',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_role' => array(
        'classname' => 'local_cveteval\\local\\external\\role',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_group_assign' => array(
        'classname' => 'local_cveteval\\local\\external\\group_assign',
        'methodname' => 'get',
        'description' => 'Get direct access to the entities stored in the db. Control check is made for some entities.',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_latest_modifications' => array(
        'classname' => 'local_cveteval\\local\\external\\latest_modifications',
        'methodname' => 'execute',
        'description' => 'Get the latest modification for a given entity type',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_submit_appraisal' => array(
        'classname' => 'local_cveteval\\local\\external\\appraisal',
        'methodname' => 'submit',
        'description' => 'Submit appraisals',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_delete_appraisal' => array(
        'classname' => 'local_cveteval\\local\\external\\appraisal',
        'methodname' => 'delete',
        'description' => 'Delete appraisal and all linked appraisal criteria',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_submit_appraisal_criteria' => array(
        'classname' => 'local_cveteval\\local\\external\\appr_crit',
        'methodname' => 'submit',
        'description' => 'Submit appraisal criteria',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'local/cveteval:mobileaccess',
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE),
    ),
    'local_cveteval_get_idplist' => array(
        'classname' => 'local_cveteval\\local\\external\\auth',
        'methodname' => 'idp_list',
        'description' => 'Get IDP list for connexion',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => '',
        'loginrequired' => false,
        'services' => array(utils::CVETEVAL_MOBILE_SERVICE)
    ),
    'local_cveteval_get_users' => array(
        'classname' => 'local_cveteval\\local\\external\\user_search',
        'methodname' => 'execute',
        'description' => 'search for users matching the parameters',
        'type' => 'read',
        'capabilities' => 'moodle/user:viewdetails, moodle/user:viewhiddendetails, moodle/course:useremail, moodle/user:update',
        'ajax' => true,
    ),
);

$services = utils::get_mobile_services_definition($functions);
