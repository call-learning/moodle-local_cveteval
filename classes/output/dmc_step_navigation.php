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
use single_button;
use stdClass;
use templatable;

/**
 * Renderable util for dmc widget
 *
 * @package    local_cveteval
 * @copyright  2020 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dmc_step_navigation implements renderable, templatable {
    private $nextstep;
    private $previousstep;

    public function __construct(data_migration_controller $dmc) {
        $this->nextstep = $dmc->get_next_step();
        $this->previousstep = $dmc->get_previous_step();
    }

    public function export_for_template(renderer_base $output) {
        global $PAGE;
        $context = new stdClass();
        if (!empty($this->nextstep)) {
            $nextpageurl = $PAGE->url;
            $nextpageurl->remove_all_params();
            $nextpageurl->param('step', $this->nextstep);
            $sb = new single_button($nextpageurl, get_string('next'));
            $context->nextbutton = $sb->export_for_template($output);
        }
        if (!empty($this->previousstep)) {
            $nextpageurl = $PAGE->url;
            $nextpageurl->remove_all_params();
            $nextpageurl->param('step', $this->previousstep);
            $sb = new single_button($nextpageurl, get_string('previous'));
            $context->previousbutton = $sb->export_for_template($output);
        }
        return $context;
    }
}
