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
 * Version details.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2023060201;      // The current module version (Date: YYYYMMDDXX).
$plugin->requires = 2020061500;      // Requires this Moodle version (3.9.1).
$plugin->maturity = MATURITY_RC;
$plugin->release = '2.0.1'; // No more specific course fields.
$plugin->component = 'local_cveteval';// Full name of the plugin (used for diagnostics).
$plugin->cron = 0;
$plugin->dependencies = [
    'local_cltools' => 2022100101
];
