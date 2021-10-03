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
 * Clinical situation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\situation;

use core\persistent;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\editor;
use local_cltools\local\field\entity_selector;
use local_cltools\local\field\text;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\model_with_history_impl;

defined('MOODLE_INTERNAL') || die();

/**
 * Clinical situation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent, model_with_history {

    use enhanced_persistent_impl;
    use model_with_history_impl;

    const TABLE = 'local_cveteval_clsituation';

    /**
     * Appraiser
     */
    const SITUATION_TYPE_APPRAISER = 'appraiser';

    /**
     * Student
     */
    const SITUATION_TYPE_STUDENT = 'student';

    /**
     * Define fields
     *
     * @return array
     */
    public static function define_fields(): array {
        return [
            new text('title'),
            new editor('description'),
            new text([
                'fieldname' => 'idnumber',
                'rawtype' => PARAM_ALPHANUMEXT
            ]),
            new text(
                [
                    'fieldname' => 'expectedevalsnb',
                    'rawtype' => PARAM_INT
                ]),
            new entity_selector([
                'fieldname' => 'evalgridid',
                'entityclass' => \local_cveteval\local\persistent\evaluation_grid\entity::class,
                'displayfield' => 'name'
            ])
        ];

    }
}


