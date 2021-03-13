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
 * Renderable for grade widget
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\output;

use renderable;
use renderer_base;
use templatable;

defined('MOODLE_INTERNAL') || die();

/**
 * Renderable for dynamic table
 *
 * @package    local_resourcelibrary
 * @copyright  2020 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_widget implements renderable, templatable  {

    protected $grade;

    /**
     * Constructor
     *
     * @param int $grade
     */
    public function __construct($grade) {
        $this->grade = $grade;
    }

    public function export_for_template(renderer_base $output) {
        return (object)[
            'gradeiconurl' => $output->image_url('grade/'.$this->grade, 'local_cveteval')->out(false),
            'gradetext' => get_string('grade:'. $this->grade, 'local_cveteval')
        ];
    }
}



