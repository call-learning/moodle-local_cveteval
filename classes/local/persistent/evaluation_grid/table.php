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

namespace local_cveteval\local\persistent\evaluation_grid;

use coding_exception;
use context;
use local_cltools\local\crud\generic\generic_entity_table;

/**
 * Evaluation grid table
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class table extends generic_entity_table {
    /**
     * @var string $persistentclass current persistent class
     */
    protected static $persistentclass = entity::class;

    /**
     * Sets up the page_table parameters.
     *
     * @param null $uniqueid
     * @param null $actionsdefs
     * @see page_list::get_filter_definition() for filter definition
     */
    public function __construct($uniqueid = null,
            $actionsdefs = null
    ) {
        parent::__construct(
            $uniqueid,
            $actionsdefs,
            false,
            (object) ['genericpersistentclass' => entity::class]
        );
    }
}
