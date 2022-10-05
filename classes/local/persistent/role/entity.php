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
 * Evaluation role
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\role;

use core\persistent;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\entity_selector;
use local_cltools\local\field\select_choice;
use local_cltools\local\field\generic_selector;
use local_cveteval\local\persistent\model_with_history;
use local_cveteval\local\persistent\model_with_history_impl;

/**
 * Evaluation role entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent, model_with_history {

    use enhanced_persistent_impl;
    use model_with_history_impl;

    const ROLE_STUDENT_ID = 0;
    const ROLE_APPRAISER_ID = 1;
    const ROLE_ASSESSOR_ID = 2;

    const ROLE_SHORTNAMES = [
            self::ROLE_STUDENT_ID => 'student',
            self::ROLE_APPRAISER_ID => 'appraiser',
            self::ROLE_ASSESSOR_ID => 'assessor',
    ];

    const TABLE = 'local_cveteval_role';

    public static function define_fields(): array {
        return [
                new generic_selector(['fieldname' => 'userid', 'type' => 'user']),
                new entity_selector([
                                'fieldname' => 'clsituationid',
                                'entityclass' => \local_cveteval\local\persistent\situation\entity::class,
                                'displayfield' => 'title',
                        ]
                ),
                new select_choice([
                        'fieldname' => 'type',
                        'choices' => [
                                self::ROLE_APPRAISER_ID => self::get_type_fullname(self::ROLE_APPRAISER_ID),
                                self::ROLE_ASSESSOR_ID => self::get_type_fullname(self::ROLE_ASSESSOR_ID)
                        ],
                        'editable' => true
                ]),
        ];
    }

    /**
     * Get type localised fullname
     *
     * @param $typeid
     * @return string
     */
    public static function get_type_fullname($typeid) {
        $shortname = static::get_type_shortname($typeid);
        return get_string("role:type:$shortname", 'local_cveteval');
    }

    /**
     * Get type shortname
     *
     * @param $typeid
     * @return string
     */
    public static function get_type_shortname($typeid) {
        return static::ROLE_SHORTNAMES[$typeid] ?? 'unknown';
    }
}


