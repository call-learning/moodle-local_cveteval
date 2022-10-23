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

namespace local_cveteval\local\persistent\import_log;

use coding_exception;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\blank_field;
use local_cltools\local\field\hidden;
use local_cltools\local\field\number;
use local_cltools\local\field\select_choice;
use local_cltools\local\field\text;
use tool_importer\local\log_levels;
use tool_importer\local\logs\import_log_entity;

/**
 * Import log entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends import_log_entity implements enhanced_persistent {
    use enhanced_persistent_impl;
    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     * @throws coding_exception
     */
    public static function define_fields(): array {
        return [
                new number(['fieldname' => 'linenumber', 'fullname' => get_string('log:linenumber', 'local_cveteval')]),
                new text(['fieldname' => 'fieldname', 'fullname' => get_string('log:fieldname', 'local_cveteval')]),
                new select_choice(['fieldname' => 'level', 'choices' =>
                        [
                                log_levels::LEVEL_INFO,
                                log_levels::LEVEL_WARNING,
                                log_levels::LEVEL_ERROR,
                        ],
                        'fullname' => get_string('log:level', 'local_cveteval')
                ]),
                new text(['fieldname' => 'origin', 'fullname' => get_string('log:origin', 'local_cveteval')]),
                new hidden('messagecode'),
                new hidden('module'),
                new blank_field(['fieldname' => 'information', 'fullname' => get_string('log:information', 'local_cveteval')]),
                new number(['fieldname' => 'importid', 'fullname' => get_string('log:importid', 'local_cveteval')]),
                new hidden('validationstep'),
                new hidden('additionalinfo'),
        ];
    }
}


