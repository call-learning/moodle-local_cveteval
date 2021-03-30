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
 * Final evaluation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\final_evaluation;

use coding_exception;
use local_cltools\local\crud\form\entity_form;

defined('MOODLE_INTERNAL') || die();

/**
 * Final evaluation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends entity_form {

    /**
     * @param \MoodleQuickForm $mform
     * Additional definitions for the form
     * @throws \dml_exception
     */
    protected function pre_field_definitions(&$mform) {
        global $OUTPUT;
        if ($studentid = $this->get_persistent()->get('studentid')) {
            $studentuser = \core_user::get_user($studentid);
            $fullname = fullname($studentuser);
            $userpicture = $OUTPUT->user_picture($studentuser);
            $mform->addElement('html',
                \html_writer::div(
                    \html_writer::div($userpicture)
                    . \html_writer::div($fullname), 'f-item'));
        }

    }

    /**
     * @param \MoodleQuickForm $mform
     * Additional definitions for the form
     */
    protected function post_field_definitions(&$mform) {
        global $OUTPUT;
        foreach (['tabname'] as $fieldtocheck) {
            if (empty($this->_customdata[$fieldtocheck])) {
                throw new coding_exception($fieldtocheck . ' must be defined');
            }
            $value = $this->_customdata[$fieldtocheck];
            $mform->setDefault('evalplanid', $value);
            $this->set_data([$fieldtocheck => $value]);
        }
    }

    /** @var string The fully qualified classname. */
    protected static $persistentclass = entity::class;

    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = array('submitbutton');

    /** @var string[] $foreignfields */
    protected static $foreignfields = array();
}
