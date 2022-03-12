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
use context_user;
use core_text;
use csv_import_reader;
use dml_exception;
use local_cveteval\local\persistent\history\entity as history_entity;
use moodle_exception;
use moodleform;
use tool_importer\processor;

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

    const VALIDATION_IMPORTID = -1;

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

        $mform->addElement('text', 'importidnumber', get_string('import:idnumber', 'local_cveteval'));
        $defaultidnumber = userdate(time(), get_string('strftimedatetime'));
        $mform->setDefault('importidnumber', $defaultidnumber);
        $mform->addHelpButton('importidnumber', 'import:idnumber', 'local_cveteval');
        $mform->setType('importidnumber', PARAM_TEXT);
        $mform->addRule('importidnumber', null, 'required');

        $mform->addElement('textarea', 'importcomment', get_string('import:comment', 'local_cveteval'),
                'wrap="virtual" rows="3" cols="40"');
        $mform->addHelpButton('importcomment', 'import:comment', 'local_cveteval');
        $mform->setType('importcomment', PARAM_TEXT);

        foreach (self::get_files_to_upload() as $filetype => $settings) {
            $fieldtype = $filetype . 'file';
            $mform->addElement('filepicker', $fieldtype,
                    get_string('import:' . $filetype, 'local_cveteval'),
                    null,
                    ['subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['csv']]
            );
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

        $this->add_action_buttons(false, get_string('import:start', 'local_cveteval'));
    }

    /**
     * Get list of file we want to upload
     *
     * @return array
     */
    public static function get_files_to_upload() {
        return array(
                'evaluation_grid' => ['required' => false, 'order' => 1],
                'grouping' => ['required' => true, 'order' => 2],
                'situation' => ['required' => true, 'order' => 3],
                'planning' => ['required' => true, 'order' => 4],
        );
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
        global $USER;
        $errors = [];
        if (history_entity::record_exists_select('idnumber = :idnumber', ['idnumber' => $data['importidnumber']])) {
            $existing = history_entity::get_record(['idnumber' => $data['importidnumber']]);
            $errors['importidnumber'] = get_string('import:error:idnumberexists', 'local_cveteval',
                    $existing->get('id') . ' ' . $existing->get('idnumber'));
        }

        $delimiter = $data['delimiter'];
        $encoding = $data['encoding'];
        $randomvalidationid = -rand(1, 50);
        $fs = get_file_storage();
        $context = context_user::instance($USER->id);
        foreach (self::get_files_to_upload_by_order() as $filetype) {
            $importclass = "\\local_cveteval\\local\\importer\\{$filetype}\\import_helper";
            if (!class_exists($importclass)) {
                throw new moodle_exception('importclassnotfound', 'local_cveteval', null, ' class:' . $importclass);
            }
            $fieldname = $filetype . 'file';
            if (!empty($data[$fieldname])) {

                $files = $fs->get_directory_files($context->id, 'user', 'draft', $data[$fieldname], '/', false, false);
                if (!empty($files)) {
                    $file = end($files);
                    $filepath = $file->copy_content_to_temp();
                    $importhelper = new $importclass($filepath, $randomvalidationid, $file->get_filename(), $delimiter, $encoding);
                    $importhelper->validate(['fastcheck' => true]);
                    $processor = $importhelper->get_processor();
                    /* @var processor processor  */
                    foreach ($processor->get_validation_log() as $log) {
                        if (empty($errors[$fieldname])) {
                            $errors[$fieldname] = $log->get_full_message();
                        } else {
                            $errors[$fieldname] .= '<br>' . $log->get_full_message();
                        }
                    }
                    $importhelper->get_processor()->purge_validation_logs();
                    unlink($filepath);
                }
            }
        }
        return $errors;
    }

    public static function get_files_to_upload_by_order() {
        $filesbyorder = array_map(function($ft) {
            return $ft['order'];
        },
                self::get_files_to_upload());
        $filesbyorder = array_flip($filesbyorder);
        ksort($filesbyorder);
        return $filesbyorder;
    }

    public function get_draft_file_from_elementname($elementname) {
        $draftfiles = $this->get_draft_files($elementname);
        return empty($draftfiles) ? null : reset($draftfiles);
    }
}
