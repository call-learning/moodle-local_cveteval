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
namespace local_cveteval\output\helpers;

use core_user;
use local_cveteval\local\persistent\criterion\entity as criterion_entity;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\group_assignment\entity as group_assignment_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;

/**
 * Renderable for userdatamigration controller
 *
 * @package    local_cveteval
 * @copyright  2020 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class output_helper {

    public static function export_entity_situation($entityid) {
        $newentity = new situation_entity($entityid);
        $situation = ['label' => $newentity->get('title') . '(' . $newentity->get('idnumber') . ')'];
        return $situation;
    }

    public static function export_entity_role($entityid) {
        $newentity = new role_entity($entityid);
        $user = core_user::get_user($newentity->get('userid'));
        $situation = new situation_entity($newentity->get('clsituationid'));
        $role = ['label' => fullname($user) . ' (' . role_entity::get_type_fullname($newentity->get('type')) . ') - '
                . $situation->get('idnumber')];
        return $role;
    }

    public static function export_entity_criterion($entityid, $shortlabel = false) {
        $newentity = new criterion_entity($entityid);
        if ($shortlabel) {
            return ['label' => $newentity->get('idnumber')];
        }
        $criterion = ['label' => $newentity->get('label') . '(' . $newentity->get('idnumber') . ')'];
        return $criterion;
    }

    public static function export_entity_group_assignment($entityid) {
        $newentity = new group_assignment_entity($entityid);
        $student = core_user::get_user($newentity->get('studentid'));
        $group = new group_entity($newentity->get('groupid'));
        $groupa = ['label' => fullname($student) . ' - ' . $group->get('name')];
        return $groupa;
    }

    public static function export_entity_evaluation_grid($entityid) {
        $newentity = new evaluation_grid_entity($entityid);
        $evalgrid = ['label' => $newentity->get('name') . '(' . $newentity->get('idnumber') . ')'];
        return $evalgrid;
    }

    public static function export_entity_group($entityid) {
        $newentity = new group_entity($entityid);
        $group = ['label' => $newentity->get('name')];
        return $group;
    }

    public static function export_entity_planning($entityid) {
        $newentity = new planning_entity($entityid);
        $situation = new situation_entity($newentity->get('clsituationid'));
        $group = new group_entity($newentity->get('groupid'));
        $planning =
                ['label' =>
                        $newentity->get_starttime_string() . '/' . $newentity->get_endtime_string()
                        . ' - ' .
                        $group->get('name')
                        . ' / ' . $situation->get('idnumber')];
        return $planning;
    }
}