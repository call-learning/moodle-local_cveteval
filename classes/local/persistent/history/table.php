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
 * History table
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\persistent\history;

use coding_exception;
use local_cltools\local\crud\entity_table;
use moodle_exception;
use moodle_url;
use pix_icon;

/**
 * Persistent import log
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends entity_table {
    /**
     * @var string $persistentclass current persistent class
     */
    protected static $persistentclass = entity::class;

    /**
     * Sets up the page_table parameters.
     *
     * @param null $uniqueid
     * @param null $actionsdefs
     * @param bool $editable
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct($uniqueid = null,
            $actionsdefs = null,
            $editable = false
    ) {
        parent::__construct($uniqueid, $actionsdefs, true);
    }

    /**
     * Format the origin field
     *
     * @param object $row
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    protected function col_tools($row) {
        global $OUTPUT, $CFG;
        $buttons = '';
        $returnurl = new moodle_url('/local/cveteval/admin/importindex.php');
        $url = new moodle_url($CFG->wwwroot . '/local/cveteval/admin/export.php',
                ['importid' => $row->id, 'returnurl' => $returnurl]);
        if (has_capability('local/cveteval:exportall', \context_system::instance())) {
            $buttons = $OUTPUT->action_icon($url, new pix_icon('t/download', get_string('download:model', 'local_cveteval')));
        }
        $url = new moodle_url($CFG->wwwroot . '/local/cveteval/manage/index.php',
                ['importid' => $row->id, 'returnurl' => $returnurl]);
        if (has_capability('local/cveteval:manageentities', \context_system::instance())) {
            $buttons .= $OUTPUT->action_icon($url, new pix_icon('t/edit', get_string('cveteval:manageentities', 'local_cveteval')));
        }
        $url = new moodle_url($CFG->wwwroot . '/local/cveteval/admin/cleanup.php',
                ['importid' => $row->id, 'type' => 'model', 'returnurl' => $returnurl]);
        if (has_capability('local/cveteval:cleanupdata', \context_system::instance())) {
            $buttons .= $OUTPUT->action_icon($url, new pix_icon('t/delete', get_string('cleanup:model', 'local_cveteval')));
        }
        $url = new moodle_url($CFG->wwwroot . '/local/cveteval/admin/cleanup.php',
                ['importid' => $row->id, 'type' => 'userdata', 'returnurl' => $returnurl]);
        if (has_capability('local/cveteval:cleanupdata', \context_system::instance())) {
            $buttons .= $OUTPUT->action_icon($url, new pix_icon('t/reset', get_string('cleanup:userdata', 'local_cveteval')));
        }
        return $buttons;
    }
}
