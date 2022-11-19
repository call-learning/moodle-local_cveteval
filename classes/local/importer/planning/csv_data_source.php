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
 * Planning Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\planning;

use cache;
use cache_store;
use tool_importer\field_types;
use tool_importer\local\exceptions\importer_exception;
/**
 * Class csv_data_source
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_data_source extends \tool_importer\local\source\csv_data_source {
    /**
     * Planning importer cache
     */
    const PLANNING_IMPORTER_CACHE_NAME = 'planningimportercache';
    /**
     * @var array $groupcolumns group columns
     */
    protected $groupcolumns = [];

    /**
     * A bit of a specific implementation for variable number of columns
     *
     * @return array
     * @throws importer_exception
     */
    public function get_fields_definition() {
        $cache = cache::make_from_params(cache_store::MODE_REQUEST, 'local_cveteval',
                self::PLANNING_IMPORTER_CACHE_NAME);
        if (!$cache->has('columns')) {
            $additionalcolumns = [
                    'Date début' => [
                            'type' => field_types::TYPE_TEXT,
                            'required' => true
                    ],
                    'Date fin' => [
                            'type' => field_types::TYPE_TEXT,
                            'required' => true
                    ]
            ];
            if (!$this->csvimporter) {
                throw new importer_exception('planning:nocolumnsdefined', importer_exception::ROW_HEADER_INDEX,
                        '',
                        'local_cveteval');
            }

            $allcolumns = $this->csvimporter->get_columns();
            if (count($allcolumns) <= 2) {
                throw new importer_exception('planning:nogroupdefined', importer_exception::ROW_HEADER_INDEX,
                        '', 'local_cveteval');
            }
            $allgroups = array_slice($allcolumns, 2);
            foreach ($allgroups as $colname) {
                $additionalcolumns[$colname] =
                        [
                                'type' => field_types::TYPE_TEXT,
                                'required' => true
                        ];
                $this->groupcolumns[] = $colname;
            }
            $cache->set('columns', $additionalcolumns);
        }
        return $cache->get('columns');
    }

    /**
     * Initialise the csv datasource.
     *
     * This will initialise the current source. This has to be called before we call current or rewind.
     *
     * @param mixed|null $options additional importer options
     * @throws importer_exception
     */
    public function init_and_check($options = null) {
        parent::init_and_check();
    }
}

