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
 * Import elements
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\local\forms\cveteval_import_form;
use local_cveteval\local\importer\importid_manager;

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");

admin_externalpage_setup('cvetevaluserview');
require_capability('local/cveteval:viewallsituations', context_system::instance());
$userid = optional_param('userid', null, PARAM_INT);
$PAGE->set_title(get_string('userview', 'local_cveteval'));
$PAGE->set_heading(get_string('userview', 'local_cveteval'));
$currenturl = new moodle_url('/local/cveteval/admin/userview.php');
$PAGE->set_url($currenturl);
$usercache = cache::make_from_params(cache_store::MODE_APPLICATION, 'local_cveteval', 'userlist');
if (!$usercache->has('alluserids')) {
    global $DB;
    $userids = [];
    foreach ($DB->get_recordset('user',null, 'lastname ASC, firstname ASC' ) as $user) {
        $userdisplay = ucwords(fullname($user)) . " ($user->email)";
        $usercache->set($user->id, $userdisplay);
        $userids[] = $user->id;
    }
    $usercache->set('alluserids', $userids);
}
$userids = $usercache->get('alluserids');
$users = [];
foreach ($userids as $id) {
    $users[$id] = $usercache->get($id);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('userview', 'local_cveteval'));

$select = new single_select($currenturl, 'userid', $users, $userid);
$select->label = get_accesshide(get_string('language'));
$select->class = 'langmenu';
echo $OUTPUT->render($select);

if ($userid) {
        global $USER;
        $olduser = $USER;
        $USER = \core_user::get_user($userid);
        $plans  = \local_cveteval\local\external\evalplan::get();
        foreach($plans as $p) {
            var_dump($p);
        }
        $USER = $olduser;

}
echo $OUTPUT->footer();
