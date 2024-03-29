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

use local_cveteval\test\test_utils;
use local_cveteval\utils;

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
debugging() || defined('BEHAT_SITE_RUNNING') || die();

global $CFG;
require_once($CFG->libdir . '/clilib.php');

// Get the cli options.
list($options, $unrecognized) = cli_get_params([
    'help' => false,
    'cleanup' => false,
    'users' => false,
    'planning' => false,
    'appraisals' => false,
    'sampletype' => 'default'
], [
    'c' => 'cleanup',
    'u' => 'users',
    'a' => 'appraisals',
    'p' => 'planning',
    's' => 'sampletype'
]);

$help =
    "php local/cveteval/cli/setup_dev.php -c

Import the definitions from the fixtures CSV file
    -c : cleanup before importing (empty the related tables before importing again)
    -p : import planning, evalgrid, situations
    -u : user importation (add users)
    -a : add appraisals
    -s : sample type (default, short...)";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

$cleanup = !empty($options['cleanup']) && $options['cleanup'];

$basepath = $CFG->dirroot . '/local/cveteval/tests/fixtures/';
$sampletype = [
    'default' => [
        'users' => '/local/cveteval/tests/fixtures/users.csv',
        'cveteval' => [
            'evaluation_grid' => "/Sample_Evalgrid.csv",
            'situation' => "/Sample_Situations.csv",
            'planning' => "/Sample_Planning.csv",
            'grouping' => "/Sample_Grouping.csv"
        ]
    ],
    'short' => [
        'users' => '/local/cveteval/tests/fixtures/ShortSample_Users.csv',
        'cveteval' => [
            'evaluation_grid' => "/Sample_Evalgrid.csv",
            'situation' => "/ShortSample_Situations.csv",
            'planning' => "/ShortSample_Planning.csv",
            'grouping' => "/ShortSample_Grouping.csv"
        ]
    ]
];
utils::create_scale_if_not_present();
utils::setup_mobile_service(true);

if (!empty($options['users']) && $options['users']) {
    cli_writeln('Import users...');
    test_utils::import_sample_users($CFG->dirroot. $sampletype[$options['sampletype']]['users']);
    cli_writeln('Users imported...');
}
if (!empty($options['planning']) && $options['planning']) {
    cli_writeln('Import planning...');
    test_utils::import_sample_planning($sampletype[$options['sampletype']]['cveteval'], $basepath, $cleanup);
    cli_writeln('Planning imported...');
}

if (!empty($options['appraisals']) && $options['appraisals']) {
    cli_writeln('Import appraisals...');
    test_utils::create_random_appraisals($cleanup);
    cli_writeln('Appraisals created...');
}
