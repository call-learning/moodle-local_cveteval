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
global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'input' => null,
    'type' => 'situation',
    'cleanup' => false
], [
    'h' => 'help',
    'i' => 'input',
    't' => 'type',
    'c' => 'cleanup'
]);

$help =
    "php local/cveteval/cli/import.php -i csvfile.csv -t situation

Import the definitions from a CSV file

    -i : input files - format defined in samples csv in doc folder
    -t : type
        The type of import can be (format defined in samples csv in doc folder):
        * planning: import the planning (depends on previously imported clinical situation)
        * situation: clinical situation
        * grouping: groups (depends on previously imported planning)
        * questions: question for evaluation
    -c : cleanup before importing (empty the related tables before importing again) 
";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

if (!file_exists($options['input'])) {
    cli_error(get_string('filenotfound', 'error') . ' input:' . $options['input']);
    die();
}
$importclass = "\\local_cveteval\\local\\importer\\{$options['type']}\\import";
if (!class_exists($importclass)) {
    cli_error(get_string('importclassnotfound', 'local_cveteval') . ' class:' . $importclass);
    die();
}
if (!empty($options['cleanup']) && $options['cleanup']) {
    $importclass::cleanup();
}
$importclass::import($options['input']);