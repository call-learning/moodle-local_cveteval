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

namespace local_cveteval\local\importer;
use local_cveteval\local\persistent\history\entity as history_entity;

defined('MOODLE_INTERNAL') || die();


/**
 * Import id manager
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class importid_manager {
    /**
     * @var mixed|null
     */
    private $importid;

    /**
     * Constructor
     * Create and set importid
     *
     * @param string $idnumber
     * @param string $comments
     * @throws \coding_exception
     */
    public function __construct($idnumber = "", $comments = "") {
            if (empty($idnumber)) {
                $dateandrandom = userdate(time(), get_string('strftimedatetimeshort')) . '-' . random_string(5);
                $idnumber = get_string('defaulthistoryidnumber', 'local_cveteval', $dateandrandom);
            }
            $history = new history_entity(0,
                (object) ['idnumber' => $idnumber, 'comments' => $comments, 'isactive' => false]);
            $history->create();
            $this->importid = $history->get('id');
            history_entity::set_current_id($this->importid);
    }

    /**
     * Destruct and disable current id
     */
    public function __destruct() {
        history_entity::reset_current_id();
    }

    public function get_importid() {
        return $this->importid;
    }
}
