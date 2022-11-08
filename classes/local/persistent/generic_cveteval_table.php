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

namespace local_cveteval\local\persistent;

use local_cltools\local\crud\entity_table;

/**
 * Persistent dynamically instanciated
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generic_cveteval_table extends entity_table {

    /**
     * @var string $genericpersistentclass
     */
    private $genericpersistentclass;

    /**
     * Constructor for dynamic table
     *
     * @param string $uniqueid a random unique id
     * @param array $actionsdefs an array of action
     * @param bool $editable is the table editable ?
     * @param string $genericclassname
     */
    public function __construct(string $uniqueid,
            array $actionsdefs,
            bool $editable,
            string $genericclassname
    ) {
        $this->genericpersistentclass = $genericclassname;
        parent::__construct($uniqueid, $actionsdefs, $editable);
    }

    /**
     * Can be overriden
     *
     * @return string|null
     */
    public function define_class() {
        return $this->genericpersistentclass;
    }

    /**
     * Get persistent class
     * @return string
     */
    public function get_persistent_class() {
        return $this->genericpersistentclass;
    }
}
