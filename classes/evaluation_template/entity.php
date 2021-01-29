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
 * Evaluation template entity
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveval\evaluation_template;

defined('MOODLE_INTERNAL') || die();

/**
 * Class rotation
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends \core\persistent {
    const TABLE = 'local_competveteval_evaltpl';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     */
    protected static function define_properties() {
        return array(
            'name' => array(
                'type' => PARAM_ALPHANUMEXT,
                'default' => '',
            ),
            'idnumber' => array(
                'type' => PARAM_ALPHANUMEXT,
                'default' => '',
            ),
            'scaleid' => array(
                'type' => PARAM_INT,
            ),
            'version' => array(
                'type' => PARAM_INT,
            )
        );
    }

    public function get_associated_questions() {
        $id = $this->get('id');
        if ($id) {
            global $DB;
            $associatedquestionsid =
                $DB->get_fieldset_select('local_competveteval_qevaltpl', 'qtplid', 'evaltplid = :evaltplid', ['evaltplid' => $id]);
            if ($associatedquestionsid) {
                list($sql, $params)= $DB->get_in_or_equal($associatedquestionsid,SQL_PARAMS_NAMED);
                return \local_cveval\question_template\entity::get_records_select($sql, $params);
            }
        }
        return [];
    }
}
