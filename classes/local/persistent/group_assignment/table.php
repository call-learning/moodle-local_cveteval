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

namespace local_cveteval\local\persistent\group_assignment;

use local_cveteval\local\manager\table_manager_with_access;
use local_cveteval\local\persistent\table_with_history_impl;

/**
 * Evaluation group_assignment table
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends table_manager_with_access {
    /**
     * @var string $persistentclass current persistent class
     */
    protected static $persistentclass = entity::class;
    use table_with_history_impl;

    /**
     * Check if we can do the following action
     *
     * If there is an appraisal or final evaluation we cannot edit or delete
     *
     * @param string $action
     * @param object $row
     * @return bool
     */
    protected function can_i_do(string $action, object $row): bool {
        global $DB;
        switch ($action) {
            case 'edit':
            case 'delete':
                // Check if there are any appraisal involving this user.
                if ($DB->count_records_sql('SELECT COUNT(*) FROM {local_cveteval_appraisal} ap
                                LEFT JOIN {local_cveteval_evalplan} ep ON ap.evalplanid = ep.id
                                WHERE ap.studentid = :studentid AND ep.groupid = :groupid',
                        ['groupid' => $row->groupid, 'studentid' => $row->studentid])
                ) {
                    return false;
                }
                if ($DB->count_records_sql('SELECT COUNT(*) FROM {local_cveteval_finalevl} ap
                                LEFT JOIN {local_cveteval_evalplan} ep ON ap.evalplanid = ep.id
                                WHERE ap.studentid = :studentid AND ep.groupid = :groupid',
                        ['groupid' => $row->groupid, 'studentid' => $row->studentid])
                ) {
                    return false;
                }
                break;
        }
        return true;
    }
}
