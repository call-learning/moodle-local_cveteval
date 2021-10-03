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
 * CompetVetEval cache
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$definitions = [
    'appraisals' => [
        'mode' => cache_store::MODE_APPLICATION,
    ],
    // Used to store history for data migration, at session level.
    'datamigration' => array(
        'mode' => cache_store::MODE_SESSION,
        'ttl' => 600,
    ),
    // Used to store history for data migration, at session level.
    'persistenthistory' => array(
        'mode' => cache_store::MODE_REQUEST
    ),
];
