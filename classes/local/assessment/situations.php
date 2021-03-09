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

use local_cltools\local\crud\entity_table;
use local_cltools\local\field\base;
use local_cltools\local\table\dynamic_table_sql;
use moodle_url;
use pix_icon;
use popup_action;

/**
 * Persistent list base class
 *
 * @package   local_cltools
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class situations extends entity_table {

    protected static $persistentclass = '\\local_cveteval\\local\\persistent\\situation\\entity';

    public function __construct($uniqueid, $actionsdefs = null) {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_cveteval/row-click-jumpurl','init', [
            $uniqueid,
            (new moodle_url('/local/cveteval/pages/assessment/mystudents.php'))->out(),
            (object)array('situationid' => 'id')
        ]);
        parent::__construct($uniqueid, $actionsdefs);
        $this->filteraliases = [
            'roletype' => 'role.type',
            'appraiserid' => 'role.userid'
        ];
    }

    /**
     * Set SQL parameters (where, from,....) from the entity
     *
     * This can be overridden when we are looking at linked entities.
     */
    protected function set_entity_sql() {
        $sqlfields = forward_static_call([static::$persistentclass, 'get_sql_fields'], 'entity', '');
        $from = static::$persistentclass::TABLE;
        $sql = '{'.$from.'} entity  LEFT JOIN {local_cveteval_role} role ON entity.id = role.clsituationid';
        $this->set_sql($sqlfields,$sql,'', []);
    }

    protected function col_description($row) {
        return $this->format_text($row->description, $row->descriptionformat);
    }

    /**
     * Get persistent columns definition
     *
     * @return array
     */
    protected function get_table_columns_definitions() {
        list($cols, $headers) = parent::get_table_columns_definitions();
        foreach($cols as $index => $col) {
            if ($col === 'descriptionformat') {
                unset($headers[$index]);
                unset($cols[$index]);
                unset($this->fields[$col]);
            }
        }
        return [array_values($cols), array_values($headers)];
    }
}
