<?php
// This file is part of Moodle - https://moodle.org/
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
 * CLI script to import.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\utils;

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'yes' => false,
], [
    'h' => 'help',
    'y' => 'yes',
]);

$usage = "Fix duplicated grids

Usage:
    # php fix_duplicated_grids.php --yes
    # php fix_duplicated_grids.php  [--help|-h]

Options:
    -h --help                   Print this help.
";

if (!$options['yes']) {
    $agree = cli_input(get_string('confirm') . '[y|N]');
    if ($agree !== 'y') {
        cli_writeln('Cancelled...');
        exit();
    }
}
global $DB;
// Check if there are duplicated criteria from the default grid (and sometimes even the default grid is duplicated !).
$duplicatecriteria = $DB->get_records('local_cveteval_history_mdl', ['tablename' => 'local_cveteval_criterion'], 'tableid ASC');
$originalcriteria = [];
foreach($duplicatecriteria as $duplicatedcriterion) {
    $criterion = $DB->get_record('local_cveteval_criterion', ['id' => $duplicatedcriterion->tableid]);
    if(empty($originalcriteria[$criterion->idnumber]) && empty($duplicatedcriterion->historyid)) {
        $originalcriteria[$criterion->idnumber] = $criterion->id;
    }
}
$reversedoriginalcriteria = array_flip($originalcriteria);
foreach ($duplicatecriteria as $duplicatecriterion) {
    if (isset($reversedoriginalcriteria[$duplicatecriterion->tableid])) {
        // The first criterion is the one we keep.
        continue;
    }
    $criteriatoremove = $DB->get_record('local_cveteval_criterion', ['id' => $duplicatecriterion->tableid]);
    $appraisalstochange = $DB->get_records('local_cveteval_appr_crit', ['criterionid' => $duplicatecriterion->tableid]);
    if ($appraisalstochange) {
        foreach ($appraisalstochange as $appraisal) {
            if (empty($originalcriteria[$criteriatoremove->idnumber])) {
                throw new Exception('No original criterion found for ' . $criteriatoremove->idnumber);
            }
            $appraisal->criterionid = $originalcriteria[$criteriatoremove->idnumber];
            $DB->update_record('local_cveteval_appr_crit', $appraisal);
        }
    }
    $DB->delete_records('local_cveteval_criterion', ['id' => $criteriatoremove->id]);
    $DB->delete_records('local_cveteval_history_mdl', ['id' => $duplicatecriterion->id]);
}

