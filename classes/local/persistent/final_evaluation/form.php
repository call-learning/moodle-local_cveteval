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
     * Usual properties definition for a persistent form
     *
     * @return array|array[]
     */
    protected static function get_fields_definition() {
        return array(
            'studentid' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'type' => 'hidden',
                ]
            ),
            'evalplanid' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'type' => 'hidden',
                ]
            ),
            'appraiserid' => array(
                'type' => PARAM_INT,
                'default' => 0,
                'format' => [
                    'type' => 'hidden',
                    'fullname' => get_string('appraiser', 'local_cveteval')
                ]
            )
        );
    }

    /**
     * @param \MoodleQuickForm $mform
     * Additional definitions for the form
     */
    protected function pre_field_definitions(&$mform) {
        global $OUTPUT;
        if ($this->_customdata['studentid']) {
            /* @var \core_renderer $OUTPUT */
            $studentuser = \core_user::get_user($this->_customdata['studentid']);
            $fullname = fullname($studentuser);
            $userpicture = $OUTPUT->user_picture($studentuser);
            $mform->addElement('html', \html_writer::div($userpicture, 'align-self-center')
                . \html_writer::div($fullname, 'align-self-center') );
        }

    }

    /**
     * @param \MoodleQuickForm $mform
     * Additional definitions for the form
     */
    protected function post_field_definitions(&$mform) {
        global $OUTPUT;
        if ($this->_customdata['evalplanid']) {
            $mform->setDefault('evalplanid', $this->_customdata['evalplanid']);
        }
        if ($this->_customdata['studentid']) {
            $mform->setDefault('studentid', $this->_customdata['studentid']);
        }
    }

    /** @var string The fully qualified classname. */
    protected static $persistentclass = entity::class;

    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = array('submitbutton');

    /** @var string[] $foreignfields */
    protected static $foreignfields = array();
}
