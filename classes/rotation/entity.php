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
 * Rotation entity
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveval\rotation;

defined('MOODLE_INTERNAL') || die();

/**
 * Class rotation
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends \core\persistent {
    const TABLE = 'local_competveteval_rotation';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected static function define_properties() {
        $now = time();
        return array(
            'title' => array(
                'type' => PARAM_ALPHANUMEXT,
                'default' => '',
            ),
            'description' => array(
                'type' => PARAM_RAW,
                'default' => '',
            ),
            'descriptionformat' => array(
                'type' => PARAM_INT,
                'default' => FORMAT_HTML,
            ),
            'starttime' => array(
                'type' => PARAM_INT,
                'default' => $now,
            ),
            'endtime' => array(
                'type' => PARAM_INT,
                'default' => $now,
            ),
            'mineval' => array(
                'type' => PARAM_INT,
                'default' => 1,
            ),
            'evaluationtemplateid' => array(
                'type' => PARAM_INT,
            ),
            'finalevalscaleid' => array(
                'type' => PARAM_INT
            )
        );
    }

    public function get_related_evaluation_template() {
        $evaluationtemplateid = $this->get('evaluationtemplateid');
        return \local_cveval\evaluation_template\entity::get_record(array('id' => $evaluationtemplateid));
    }
}
