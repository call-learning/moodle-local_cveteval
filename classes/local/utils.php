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
 * Various utilities
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local;

use core_user;
use grade_scale;
use \local_cveteval\local\persistent\role\entity as role_entity;
use stdClass;

defined('MOODLE_INTERNAL') || die();

class utils {
    /**
     * To get usernames in a loop faster
     *
     * @param $userid
     * @return \lang_string|string
     * @throws \dml_exception
     */
    public static function fast_user_fullname($userid) {
        static $usernames = [];
        if (empty($usernames[$userid])) {
            $usernames[$userid] = fullname(core_user::get_user($userid));
        }
        return $usernames[$userid];
    }

    /**
     * Get role identifier
     *
     * If both appraiser and assessor, assessor role will be returned
     *
     * @param $userid
     * @return int
     * @throws \dml_exception
     */
    public static function get_user_role_id($userid) {
        $roleid = role_entity::ROLE_STUDENT_ID;
        // Check that user exists first, if not it will be a student role.
        if ($user = \core_user::get_user($userid)) {
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
            if ($isappraiser) {
                $roleid = role_entity::ROLE_APPRAISER_ID;
            }
            if ($isassessor) {
                $roleid = role_entity::ROLE_ASSESSOR_ID;
            }
        }

        return $roleid;
    }

    const DEFAULT_SCALE_ITEM = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20
    ];

    public static function create_scale_if_not_present() {
        $scales = grade_scale::fetch_all_global();
        $defaultscale = null;
        foreach ($scales as $scale) {
            if ($scale->get_name() == get_string('grade:defaultscale', 'local_cveteval')) {
                $defaultscale = $scale;
            }
        }
        if (empty($defaultscale)) {
            global $USER;
            $scalerecord = new stdClass();
            $scalerecord->standard = 1;
            $scalerecord->courseid = 0;
            $scalerecord->scale = join(',', self::DEFAULT_SCALE_ITEM);
            $scalerecord->userid = $USER->id;
            $scalerecord->name = get_string('grade:defaultscale', 'local_cveteval');
            $scalerecord->description = get_string('grade:defaultscale:description', 'local_cveteval');
            $scalerecord->descriptionformat = FORMAT_PLAIN;
            $defaultscale = new grade_scale($scalerecord);
            $defaultscale->insert();
        } else {
            $defaultscale->load_items(self::DEFAULT_SCALE_ITEM);
            $defaultscale->update();
        }
    }
}
