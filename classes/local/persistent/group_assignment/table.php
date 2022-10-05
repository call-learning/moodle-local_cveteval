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


use coding_exception;
use context;
use local_cltools\local\crud\generic\generic_entity_table;
use local_cltools\local\field\blank_field;
use restricted_context_exception;

/**
 * Evaluation group_assignment table
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends generic_entity_table {
    protected static $persistentclass = entity::class;

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
     * Default property definition
     *
     * Add all the fields from persistent class except the reserved ones
     *
     * @throws ReflectionException
     */
    protected function setup_fields() {
        parent::setup_fields();
        $this->fields[] = new blank_field([
                'fieldname' => 'user',
                'fullname' => get_string('username')
        ]);
    }

    /**
     * Format the username cell.
     *
     * @param $row
     * @return string
     * @throws coding_exception
     */
    protected function col_user($row) {
        $user = \core_user::get_user($row->studentid);
        if($user) {
            return fullname($user) . "($user->email)";
        }
    }
}
