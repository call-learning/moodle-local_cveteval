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

use local_cltools\local\crud\generic\generic_entity_exporter_generator;
use local_cltools\local\crud\helper\crud_view;
use local_cveteval\local\external\external_utils;
use local_cveteval\local\forms\cveteval_import_form;
use local_cveteval\local\importer\importid_manager;
use local_cveteval\roles;

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
$renderer = $PAGE->get_renderer('local_cveteval');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('userview', 'local_cveteval'));

/**
 * User selector class.
 */
class user_select extends moodleform {

    /**
     * Define form
     *
     * @return void
     * @throws coding_exception
     */
    protected function definition() {
        $mform = $this->_form;
        $preloadeduser = [];
        if (!empty($this->_customdata['user'])) {
            $preloadeduser = $this->_customdata['user'];
        }
        // Add an autocomplete field.
        $mform->addElement('autocomplete', 'userid', get_string('user'), $preloadeduser,
            ['class' => 'userautocomplete', 'ajax' => 'local_cveteval/user_selector']);
        $mform->setType('userid', PARAM_INT);
        $mform->addElement('submit', 'submit', get_string('view'));
    }
}

if ($userid) {
    $currentuser = core_user::get_user($userid);
    $customdata = ['user' => [
        $userid => fullname($currentuser) . ' (' . $currentuser->email . ')'
    ]];
}
$select = new user_select($currenturl, $customdata);
echo $select->render();

if ($userid) {
    global $USER;
    $olduser = $USER;

    echo $OUTPUT->heading(get_string('role:type', 'local_cveteval') . ': '
        . local_cveteval\local\persistent\role\entity::get_type_fullname(roles::get_user_role_id($userid)));
    if (roles::can_appraise($userid)) {
        $situations = external_utils::query_entities('situation', [], null, $userid);
        foreach ($situations as $s) {
            if (local_cveteval\local\persistent\role\entity::count_records(['userid' => $userid,
                    'clsituationid' => $s->id]) > 0) {
                $situation = new local_cveteval\local\persistent\situation\entity($s->id);
                $exporter = generic_entity_exporter_generator::generate($situation);
                echo $OUTPUT->box_start();
                echo crud_view::display_entity($situation, $exporter, $renderer);
                echo $OUTPUT->box_end();
            }
        }
    } else {
        $plans = external_utils::query_entities('planning', [], null, $userid);
        foreach ($plans as $p) {
            $evalplan = new local_cveteval\local\persistent\planning\entity($p->id);
            $exporter = generic_entity_exporter_generator::generate($evalplan);
            echo $OUTPUT->box_start();
            echo crud_view::display_entity($evalplan, $exporter, $renderer);
            echo $OUTPUT->box_end();
        }
    }
}
echo $OUTPUT->footer();
