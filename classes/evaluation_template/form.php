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
 * Evaluation template form
 *
 * @package   local_cveval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveval\evaluation_template;

use local_cveval\form\persistent_form;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/theme/clboost/form/sortable_list.php');

/**
 * Add Form
 *
 * @package     local_cveval
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends persistent_form {

    /** @var string The fully qualified classname. */
    protected static $persistentclass = '\\local_cveval\\evaluation_template\\entity';

    /**
     * Form property in order to display the right widget for the form.
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected static function define_properties() {
        global $DB;
        $scales = $DB->get_records_menu('scale', null, '', 'id,name');
        return array(
            'name' => (object) [
                'type' => 'text',
                'fullname' => get_string('name', 'local_cveval'),
            ],
            'idnumber' => (object) [
                'type' => 'text',
                'fullname' => get_string('idnumber', 'local_cveval'),
            ],
            'scaleid' => (object) [
                'fullname' => get_string('evaluation_template:scaleid', 'local_cveval'),
                'type' => 'select_choice',
                'choices' => $scales
            ],
            'version' => (object) [
                'fullname' => get_string('evaluation_template:version', 'local_cveval'),
            ]
        );
    }

    /**
     * Additional definitions
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected function additional_definitions(&$mform) {
        //$repeatarray = array();
        //$repeatedoptions = array();
        //
        //$availablequestions = \local_cveval\question_template\entity::get_records();
        //if (!$availablequestions) {
        //    $availablequestions = [];
        //}
        //$repeatarray[] = $mform->createElement('select',
        //    'question_template',
        //    get_string('question_template', 'local_cveval'),
        //    $availablequestions
        //);
        //$repeatarray[] = $mform->createElement('button',
        //    'question_template_delete',
        //    get_string('question_template_delete', 'local_cveval'),
        //    $availablequestions
        //);
        //$repeatedoptions['question_template']['type'] = PARAM_INT;
        //$repeatedoptions['question_template_delete']['type'] = PARAM_RAW;
        //$nbquestions = empty($this->get_persistent()) ? 0 : count($this->get_persistent()->get_associated_questions());
        //$this->repeat_elements($repeatarray, $nbquestions,
        //    $repeatedoptions,
        //    'question_template_repeat', 'question_template_add', 1,
        //    get_string('addmorequestions', 'local_cveval')
        //);
        /* @var \MoodleQuickForm $mform */
        $mform->addElement('sortable_list','testlist',
            get_string('questionlist', 'local_cveval'),
        array('a'=>'AAAAA','b'=>'BBBBB','c'=>'CCCCC'));
        //$mform->hardFreeze('testlist');
    }
}