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
namespace local_cveteval\output;

use local_cveteval\local\datamigration\data_migration_controller;
use renderable;
use renderer_base;
use templatable;

/**
 * Renderable for userdatamigration controller
 *
 * @package    local_cveteval
 * @copyright  2020 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class dmc_entity_renderer_base implements renderable, templatable {
    const ALL_CONTEXTS = ['orphanedentities', 'unmatchedentities', 'matchedentities'];
    const ACTIONABLE_CONTEXTS = ['orphanedentities', 'matchedentities'];
    protected $dmc;
    protected $navigation = null;

    public function __construct(data_migration_controller $dmc) {
        $this->navigation = new dmc_step_navigation($dmc);
        $this->dmc = $dmc;
    }

    public function export_for_template(renderer_base $output) {
        return $this->navigation->export_for_template($output);
    }

}
