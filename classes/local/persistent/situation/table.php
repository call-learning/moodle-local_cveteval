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

namespace local_cveteval\local\persistent\situation;

use coding_exception;
use context;
use local_cltools\local\crud\entity_table;
use local_cltools\local\crud\entity_utils;
use local_cltools\local\crud\generic\generic_entity_table;
use local_cltools\local\field\blank_field;
use local_cveteval\local\manager\table_manager_with_access;
use local_cveteval\local\persistent\table_with_history_impl;
use restricted_context_exception;

/**
 * Situation table
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends table_manager_with_access {
    protected static $persistentclass = entity::class;
    use table_with_history_impl;
    /**
     * Sets up the page_table parameters.
     *
     * @throws coding_exception
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct($uniqueid = null,
            $actionsdefs = null,
            $editable = false
    ) {
        parent::__construct($uniqueid, $actionsdefs, true, entity::class);
    }

    /**
     * Validate current user has access to the table instance
     *
     * Note: this can involve a more complicated check if needed and requires filters and all
     * setup to be done in order to make sure we validated against the right information
     * (such as for example a filter needs to be set in order not to return data a user should not see).
     *
     * @param context $context
     * @param bool $writeaccess
     * @throws restricted_context_exception
     */
    public function validate_access(context $context, $writeaccess = false) {
        if (!has_capability('local/cltools:dynamictableread', $context)) {
            throw new restricted_context_exception();
        }
        if ($writeaccess && !has_capability('local/cltools:dynamictablewrite', $context)) {
            throw new restricted_context_exception();
        }
    }
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
                                WHERE ep.clsituationid = :situationid',
                        ['situationid' => $row->id])
                ) {
                    return false;
                }
                if ($DB->count_records_sql('SELECT COUNT(*) FROM {local_cveteval_finalevl} fe
                                LEFT JOIN {local_cveteval_evalplan} ep ON fe.evalplanid = ep.id
                                WHERE ep.clsituationid = :situationid',
                        ['situationid' => $row->id])
                ) {
                    return false;
                }
                break;
        }
        return true;
    }
}
