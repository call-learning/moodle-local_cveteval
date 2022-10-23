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

namespace local_cveteval\local\persistent\final_evaluation;
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/lib/grade/grade_scale.php');

use core\persistent;
use grade_scale;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\editor;
use local_cltools\local\field\hidden;
use local_cltools\local\field\select_choice;

defined('MOODLE_INTERNAL') || die();

/**
 * Final evaluation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent {

    use enhanced_persistent_impl;
    /**
     * Current table
     */
    const TABLE = 'local_cveteval_finalevl';

    /**
     * Define fields
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function define_fields(): array {
        $scaleid = get_config('local_cveteval', 'grade_scale');
        $scale = grade_scale::fetch(array('id' => $scaleid));
        $scaleitems = $scale->load_items();
        return [
                new hidden(['fieldname' => 'studentid', 'rawtype' => PARAM_INT]),
                new hidden(['fieldname' => 'assessorid', 'rawtype' => PARAM_INT]),
                new hidden(['fieldname' => 'evalplanid', 'rawtype' => PARAM_INT]),
                new editor('comment'),
                new select_choice(['fieldname' => 'grade', 'displayname' => get_string('evaluation:grade', 'local_cveteval'),
                        'choices' => $scaleitems])
        ];
    }
}

