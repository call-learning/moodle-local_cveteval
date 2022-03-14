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
 * Choose history form
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\forms;

use coding_exception;
use dml_exception;
use local_cveteval\local\datamigration\data_migration_controller;
use local_cveteval\local\persistent\history\entity as history_entity;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Choose history form
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dmc_choosehistory_form extends moodleform implements dmc_form_interface {

    /**
     * Form definition
     *
     * @throws coding_exception
     */
    public function definition() {
        $mform = $this->_form;
        $dmc = $this->_customdata['dmc'] ?? null;
        /* @var data_migration_controller|null $dmc Data migration controller .*/
        $histories = history_entity::get_records();
        $choices = [];
        foreach ($histories as $history) {
            $choices[$history->get('id')] = $history->get('idnumber') . ' - ' . $history->get('comments');
        }
        $mform->addElement('select', 'originimportid', get_string('import:originimportid', 'local_cveteval'), $choices);
        $mform->addElement('select', 'destimportid', get_string('import:destimportid', 'local_cveteval'), $choices);
        $mform->addRule('originimportid', null, 'required');
        $mform->addRule('destimportid', null, 'required');
        $mform->addElement('hidden', 'step');
        $mform->setType('step', PARAM_TEXT);
        if ($dmc) {
            $mform->setConstant('step', $dmc->get_step());
        }
        $this->add_action_buttons(false, get_string('import:selectimport', 'local_cveteval'));
    }

    /**
     * Validate the form after submission
     *
     * @param array $data
     * @param array $files
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files) {
        $errors = [];
        if ($data['originimportid'] == $data['destimportid']) {
            $errors['originimportid'] = get_string('import:origindestmustdiffer', 'local_cveteval');
            $errors['destimportid'] = get_string('import:origindestmustdiffer', 'local_cveteval');
        }
        return $errors;
    }

    public function execute_action($data) {
        global $PAGE;
        $dmc = $this->_customdata['dmc'] ?? null;
        /* @var data_migration_controller|null $dmc Data migration controller .*/
        if ($dmc) {
            $stepdata = (object) [
                    'originimportid' => $data->originimportid,
                    'destimportid' => $data->destimportid,
            ];
            $dmc->set_step_data($stepdata);
        }
        redirect(new moodle_url($PAGE->url, ['step' => $dmc->get_next_step()]));
    }

    public function execute_cancel() {
        // Nothing for now.
    }
}
