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
 * Final evaluation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\final_evaluation;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/lib/grade/grade_scale.php');

use coding_exception;
use core\persistent;
use grade_scale;

defined('MOODLE_INTERNAL') || die();

/**
 * Final evaluation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent {

    const TABLE = 'local_cveteval_finalevl';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws coding_exception
     */
    protected static function define_properties() {
        $scaleid = get_config('local_cveteval', 'grade_scale');
        $scale = grade_scale::fetch(array('id' => $scaleid));
        $scaleitems = $scale->load_items();
        return array(
            'studentid' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'type' => 'hidden',
                ]
            ),
            'assessorid' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'type' => 'hidden',
                ]
            ),
            'evalplanid' => array(
                'type' => PARAM_INT,
                'default' => '',
                'format' => [
                    'type' => 'hidden',
                ]
            ),
            'comment' => array(
                'type' => PARAM_RAW,
                'default' => ''
            ),
            'commentformat' => array(
                'type' => PARAM_INT,
                'default' => FORMAT_HTML
            ),

            'grade' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'fullname' => get_string('evaluation:grade', 'local_cveteval'),
                    'type' => 'select_choice',
                    'choices' => $scaleitems
                ]
            ),
        );
    }
}

