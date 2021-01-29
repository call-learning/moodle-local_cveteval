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
 * View a Rotation page
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveval\utils\crud_helper;

require_once(__DIR__ . '/../../../../config.php');

global $CFG, $OUTPUT, $PAGE;
require_login();
require_capability('local/cveval:managerotation', context_system::instance());

$crudmgmt = new crud_helper(
    '\\local_cveval\\rotation',
    crud_helper::VIEW_ACTION
);


$crudmgmt->setup_page($PAGE);

$out  = $crudmgmt->action_process($PAGE);

echo $OUTPUT->header();
echo $out;
echo $OUTPUT->footer();