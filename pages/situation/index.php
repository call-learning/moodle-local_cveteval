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
 * Main page for all clinical situation editions
 *
 * Routing is made through the action parameter.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../../config.php');
global $CFG;

use local_cltools\local\crud\helper\base as crud_helper;
use local_cltools\local\crud\helper\crud_list;

global $CFG, $OUTPUT, $PAGE;
require_login();;

$action = optional_param('action', crud_list::ACTION, PARAM_TEXT);
$entityclassname = '\\local_cveteval\\local\\persistent\\situation\\entity';

$navigation = new \local_cltools\local\crud\navigation\routed_navigation($entityclassname);
$crudmgmt = crud_helper::create(
    $entityclassname,
    $action,
    null,
    null,
    null,
    null,
    $navigation
);

$crudmgmt->setup_page($PAGE);

$out = $crudmgmt->action_process();

echo $OUTPUT->header();
echo $out;
echo $OUTPUT->footer();