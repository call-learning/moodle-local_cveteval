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
 * Import forms
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\forms;

use coding_exception;
use core_text;
use csv_import_reader;
use dml_exception;
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * Import forms
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cveteval_import_form extends moodleform {

    /**
     * Form definition
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'headingfile', get_string('headingfile', 'local_cveteval'));
        $mform->addHelpButton('headingfile', 'headingfile', 'local_cveteval');

        foreach (self::get_files_to_upload() as $filetype => $settings) {
            $fieldtype = $filetype . 'file';
            $mform->addElement('filepicker', $fieldtype, get_string('import:' . $filetype, 'local_cveteval'));
            if (!empty($settings) && !empty($settings['required']) && $settings['required']) {
                $mform->addRule($fieldtype, null, 'required');
                $mform->addHelpButton($fieldtype, $fieldtype, 'local_cveteval');
            }

        }
        $mform->addElement('header', 'headingparams', get_string('import:heading:parameters', 'local_cveteval'));

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter', get_string('csvdelimiter', 'local_cveteval'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter', 'semicolon');
        } else {
            $mform->setDefault('delimiter', 'comma');
        }

        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'local_cveteval'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $mform->addElement('advcheckbox', 'cleanupbefore',
            get_string('import:cleanupbefore', 'local_cveteval'));
        $mform->setDefault('cleanupbefore', false);

        $mform->addElement('advcheckbox', 'importviacron',
            get_string('import:importviacron', 'local_cveteval'));
        $mform->setDefault('importviacron', false);

        $this->add_action_buttons(false, get_string('import:start', 'local_cveteval'));
    }

    /**
     * Get list of file we want to upload
     *
     * @return array
     */
    public static function get_files_to_upload() {
        return array(
            'situation' => ['required' => true, 'order' => 2],
            'planning' => ['required' => true, 'order' => 3],
            'grouping' => ['required' => true, 'order' => 4],
            'evaluation_grid' => ['required' => false, 'order' => 1],
        );
    }
}
