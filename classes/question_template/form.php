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
 * Question template form
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_cveteval\question_template;
use local_cveteval\form\persistent_form;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Add Form
 *
 * @package     local_cveteval
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends persistent_form {

    /** @var string The fully qualified classname. */
    protected static $persistentclass = '\\local_cveteval\\question_template\\entity';


    /**
     * Form property in order to display the right widget for the form.
     *
     * @return array|array[]
     * @throws \coding_exception
     */
    protected static function define_properties() {
        return array(
            'type' => array(
                'fullname' => get_string('question_template:type', 'local_cveteval'),
                'type' => 'select_choice',
                'choices' => entity::get_question_types(),
            ),
            'label' => array(
                'type' => 'text',
                'fullname' => get_string('name', 'local_cveteval'),
            ),
        );
    }

}