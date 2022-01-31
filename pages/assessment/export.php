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
 * Export a list of evaluations as CSV
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '../../../../../config.php');


global $CFG, $OUTPUT, $PAGE;
require_login();
require_capability('local/cveteval:exportgrades', context_system::instance());
$PAGE->set_context(context_system::instance());

require_sesskey();

$dataformat = required_param('dataformat', PARAM_ALPHA);
$filename = \local_cveteval\local\download_helper::generate_filename('Grades_CVETEVAL');

// Download all active final evaluation.
\local_cveteval\local\download_helper::download_userdata_final_evaluation($dataformat, $filename);



