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

use local_vetagropro\locallib\setup;
define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'cleanup' => false,
    'users' => false
], [
    'c' => 'cleanup',
    'u' => 'users'
]);

$help =
    "php local/cveteval/cli/setup_dev.php -c 

Import the definitions from the fixtures CSV file
    -c : cleanup before importing (empty the related tables before importing again)
    -u : user importation (add users)  
";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

$cleanup = !empty($options['cleanup']) && $options['cleanup'];
require_once($CFG->dirroot . '/local/cveteval/tests/helpers.php');
if (!empty($options['users']) && $options['users']) {
    inport_sample_users();
}
import_sample_fixture($cleanup);