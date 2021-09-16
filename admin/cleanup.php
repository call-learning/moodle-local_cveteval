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
 * Cleanup all tables
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\utils;

define('NO_OUTPUT_BUFFERING', true);
require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup('cvetevalcleanup');
$PAGE->set_title(get_string('cleanup', 'local_cveteval'));
$PAGE->set_heading(get_string('cleanup', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/admin/cleanup.php'));
$confirm = optional_param('confirm', false, PARAM_BOOL);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('cleanup', 'local_cveteval'));
if ($confirm) {
    require_sesskey();
    utils::cleanup_all_data();
    /* @var core_renderer $OUTPUT */
    echo $OUTPUT->continue_button(
        new moodle_url('/admin/category.php', array('category' => 'cveteval'))
    );
} else {
    sesskey();
    echo $OUTPUT->confirm(
        get_string('cleanup:confirm', 'local_cveteval'),
        new moodle_url($PAGE->url, array('confirm' => 1)),
        new moodle_url('/admin/category.php', array('category' => 'cveteval'))
    );
}

echo $OUTPUT->footer();