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
use local_cltools\local\crud\entity_utils;
use local_cltools\local\field\base;
use local_cltools\local\table\dynamic_table_sql;
use moodle_url;
use pix_icon;
use popup_action;

/**
 * A list of student matching this situation
 *
 * @package   local_cltools
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class situation_students extends dynamic_table_sql {

    public function __construct($uniqueid) {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_cveteval/row-click-jumpurl','init', [
            $uniqueid,
            (new moodle_url('/local/cveteval/assess.php'))->out(),
            'studentid'
        ]);
        parent::__construct($uniqueid);
    }

    /**
     * Default property definition
     *
     * Add all the fields from persistent class except the reserved ones
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function setup_fields() {
        $fields = [
            'username' => [
                "fullname" => get_string("student"),
                "rawtype" => PARAM_TEXT,
                "type" => "text"
            ]
        ];
        $this->fields = [];
        foreach ($fields as $name => $prop) {
            $this->fields[$name] = base::get_instance_from_def($prop['type'],
                $prop
            );
        }
        $this->setup_other_fields();
    }

    /**
     * Set SQL parameters (where, from,....) from the entity
     *
     * This can be overridden when we are looking at linked entities.
     */
    protected function set_entity_sql() {
        $this->set_sql("id, username",'{user} user','1=1', []);
    }

}
