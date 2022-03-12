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
namespace local_cveteval\local\persistent\appraisal_criterion_comment;

use core\persistent;

/**
 * Appraisal criterion comment
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent {

    const TABLE = 'local_cveteval_apprq_com';

    /**
     * Usual properties definition for a persistent
     *
     * @return array|array[]
     */
    protected static function define_properties() {
        return array(
                'appraisalqtemplateid' => array(
                        'type' => PARAM_INT,
                        'default' => ''
                ),
                'userid' => array(
                        'type' => PARAM_INT,
                        'default' => ''
                ),
                'comment' => array(
                        'type' => PARAM_TEXT,
                        'default' => ''
                ),
                'commentformat' => array(
                        'type' => PARAM_INT,
                        'default' => FORMAT_PLAIN
                )
        );
    }
}

