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
 * Roles related routine
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval;

use core_user;
use dml_exception;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\role\entity as role_entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Class roles
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class roles {
    /**
     * Get role identifier
     *
     * If both appraiser and assessor, assessor role will be returned
     *
     * @param $userid
     * @return int
     * @throws dml_exception
     */
    public static function get_user_role_id($userid) {
        $roleid = role_entity::ROLE_STUDENT_ID;
        // Check that user exists first, if not it will be a student role.
        if ($user = core_user::get_user($userid)) {
            $roles = role_entity::get_records_select(
                "userid = :userid AND type IN (:appraisertype, :assessortype)",
                array(
                    'userid' => $userid,
                    'appraisertype' => role_entity::ROLE_APPRAISER_ID,
                    'assessortype' => role_entity::ROLE_ASSESSOR_ID,
                ));
            $isappraiser = false;
            $isassessor = false;
            foreach ($roles as $role) {
                if ($role->get('type') == role_entity::ROLE_APPRAISER_ID) {
                    $isappraiser = true;
                }
                if ($role->get('type') == role_entity::ROLE_ASSESSOR_ID) {
                    $isassessor = true;
                }
            }
            if (group_assignment_entity::record_exists_select('studentid =:sid', array('sid' => $userid))) {
                // As we check this globally and not per situation, this means that we ensure that a student stays a student.
                $isappraiser = false;
                $isassessor = false;
            }
            if ($isappraiser) {
                $roleid = role_entity::ROLE_APPRAISER_ID;
            }
            if ($isassessor) {
                $roleid = role_entity::ROLE_ASSESSOR_ID;
            }
        }

        return $roleid;
    }

    /**
     * Can assess ?
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public static function can_assess($userid) {
        return (self::get_user_role_id($userid) == role_entity::ROLE_ASSESSOR_ID) || is_primary_admin($userid);
    }

    public static function can_see_all_situations($userid) {
        return is_primary_admin($userid);
    }
}

