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

use core_table\local\filter\filter;
use local_cltools\local\filter\enhanced_filterset;
use local_cltools\local\filter\numeric_comparison_filter;
use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\persistent\import_log\table;

/**
 * Import log utils
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_log_utils {

    public static function get_log_table($importid) {
        $entitylist = new table();

        $filterset = new enhanced_filterset(
                [
                        'importid' => (object)
                        [
                                'filterclass' => numeric_comparison_filter::class,
                                'required' => true
                        ]
                ]
        );
        $filterset->set_join_type(filter::JOINTYPE_ALL);
        $filterset->add_filter_from_params(
                'importid', // Field name.
                filter::JOINTYPE_ALL,
                [['direction' => '=', 'value' => $importid]]
        );
        $entitylist->set_filterset($filterset);

        $renderable = new entity_table_renderable($entitylist);
        return $renderable;
    }
}
