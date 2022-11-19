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
 * Role importation failure
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\event;

use coding_exception;
use context_system;
use core\event\base;
use dml_exception;
use moodle_url;

/**
 * Role importation failure
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - string email: email of the user.
 *      - int reason: failure reason.
 * }
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class role_importation_failed extends base {
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('roleimportationfailed', 'local_cveteval');
    }

    /**
     * Get mapping for other fields
     *
     * @return false
     */
    public static function get_other_mapping() {
        return false;
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description() {
        // Note that username could be any random user input.
        $email = s($this->other['email']);
        $reasonid = $this->other['reason'];
        $roleimportation = 'Role importation failed';
        switch ($reasonid) {
            case 1:
                return $roleimportation . " '{$email}'. User does not exist (error ID '{$reasonid}').";
            default:
                return $roleimportation . " '{$email}', error ID '{$reasonid}'.";

        }
    }

    /**
     * Get URL related to the action.
     *
     * @return moodle_url|null
     */
    public function get_url() {
        return null;
    }

    /**
     * Init method.
     *
     * @return void
     * @throws dml_exception
     */
    protected function init() {
        $this->context = context_system::instance();
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Custom validation.
     *
     * @return void
     * @throws coding_exception when validation does not pass.
     */
    protected function validate_data() {
        parent::validate_data();

        if (!isset($this->other['reason'])) {
            throw new coding_exception('The \'reason\' value must be set in other.');
        }

        if (!isset($this->other['email'])) {
            throw new coding_exception('The \'email\' value must be set in other.');
        }
    }
}
