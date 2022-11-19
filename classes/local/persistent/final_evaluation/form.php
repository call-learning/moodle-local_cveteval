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
use MoodleQuickForm;

/**
 * Final evaluation entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends entity_form {

    /**
     * Field for tabname
     */
    const PAGE_TABNAME_FIELD = 'tabname';
    /** @var string The fully qualified classname. */
    protected static $persistentclass = entity::class;
    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = array('submitbutton');
    /** @var string[] $foreignfields */
    protected static $foreignfields = [self::PAGE_TABNAME_FIELD];

    /**
     * Field definition before setting up the form completely
     *
     * @param MoodleQuickForm $mform
     * Additional definitions for the form
     * @throws coding_exception
     */
    protected function pre_field_definitions(&$mform) {
        if (empty($this->_customdata[self::PAGE_TABNAME_FIELD])) {
            throw new coding_exception(self::PAGE_TABNAME_FIELD . ' must be defined');
        }
        $value = $this->_customdata[self::PAGE_TABNAME_FIELD];
        $mform->addElement('hidden', self::PAGE_TABNAME_FIELD, $value);
        $mform->setType(self::PAGE_TABNAME_FIELD, PARAM_ALPHANUM);
    }
}
