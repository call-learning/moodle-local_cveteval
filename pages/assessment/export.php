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

use core\plugininfo\dataformat;
use local_cveteval\local\utils;

global $CFG, $OUTPUT, $PAGE;
require_login();
require_capability('local/cveteval:exportgrades', context_system::instance());
$dataformat = required_param('dataformat', PARAM_ALPHA);
$PAGE->set_context(context_system::instance());
$filename = str_replace(' ', '_', clean_filename('Grades_CVETEVAL-' . userdate(time())));

require_sesskey();
global $DB;

$rs = $DB->get_recordset(local_cveteval\local\persistent\final_evaluation\entity::TABLE);

$fields =
    ['studentname', 'studentemail', 'studentusername', 'assessorname', 'assessoremail', 'assessorusername',
        'grade', 'comment', 'timemodified', 'timecreated'];

// In 3.9 we could directly use the download_data function.

$transformcsv = function($finaleval) {
    $student = core_user::get_user($finaleval->studentid);
    $assessor = core_user::get_user($finaleval->assessorid);
    return [
        'studentname' => utils::fast_user_fullname($finaleval->studentid),
        'studentemail' => $student->email,
        'studentusername' => $student->username,
        'assessorname' => utils::fast_user_fullname($finaleval->assessorid),
        'assessoremail' => $assessor->email,
        'assessorusername' => $assessor->username,
        'grade' => $finaleval->grade,
        'comment' => html_to_text(format_text($finaleval->comment, $finaleval->commentformat)),
        'timemodified' => userdate($finaleval->timemodified, get_string('strftimedatetime', 'core_langconfig')),
        'timecreated' => userdate($finaleval->timecreated, get_string('strftimedatetime', 'core_langconfig')),
    ];
};
if (method_exists('dataformat', 'download_data')) {
    dataformat::download_data($filename, $dataformat, $fields, $rs, $transformcsv);
} else {
    require_once($CFG->libdir . '/dataformatlib.php');
    download_as_dataformat($filename, $dataformat, $fields, $rs, $transformcsv);
}




