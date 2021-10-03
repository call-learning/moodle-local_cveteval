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
use local_vetagropro\locallib\setup;

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

$usage = "Reset all datatable

Usage:
    # php reset_data.php --yes
    # php reset_data.php  [--help|-h]

Options:
    -h --help                   Print this help.
";

if (!$options['yes']) {
    $agree = cli_input(get_string('reset:confirm', 'local_cveteval') . '[y|N]');
    if ($agree !== 'y') {
        cli_writeln('Cancelled...');
        exit();
    }
}

utils::reset_all_data();
utils::create_update_default_criteria_grid();