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
 * External services
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\external;

use context_system;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_value;

/**
 * Class base_get_entity
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_get_entity extends external_api {
    /**
     * Field matcher
     */
    const MOBILE_ENTITY_MATCHER = [
            'appr_crit' => 'appraisal_criterion',
            'appraisal' => 'appraisal',
            'clsituation' => 'situation',
            'evalplan' => 'planning',
            'criterion' => 'criterion',
            'group_assign' => 'group_assignment',
            'role' => 'role'
    ];

    /**
     * Returns description of method parameters
     *
     * @return external_multiple_structure
     */
    abstract public static function get_returns();

    /**
     * Get query and return elements
     *
     * @param string $query
     * @return array
     */
    public static function get($query = null) {
        $classname = static::class;
        $baseclassname = substr($classname, strrpos($classname, '\\') + 1);
        return self::basic_get(self::MOBILE_ENTITY_MATCHER[$baseclassname], $query);
    }

    /**
     * Basic query get
     * @param string $entityname
     * @param object $query
     * @return array|false
     */
    public static function basic_get($entityname, $query) {
        // TODO: leverage the persistent entities features to get the right columns/fields to return.
        $params = self::validate_parameters(self::get_parameters(), array('query' => $query));
        $queryobject = [];
        if (!empty($params['query'])) {
            $queryobject = json_decode($params['query']);
        }
        $context = context_system::instance();
        self::validate_context($context);
        return external_utils::query_entities($entityname, $queryobject);
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_parameters() {
        return new external_function_parameters(
                array(
                        'query' => new external_value(PARAM_NOTAGS, 'query as json {field:value, field:value}',
                                VALUE_DEFAULT,
                                '{}'),
                )
        );
    }

    // Due to changes in the API  AND mobile version, the entities in Moodle have slightly
    // different names than the relevant tables. We will need to uniformise this at some point.

    /**
     * Submit new entities, no check
     *
     * TODO: check for user right at submission
     *
     * @param array $entityarray
     * @param string $entityclass
     * @return array
     */
    protected static function entities_submit($entityarray, $entityclass) {
        $entities = [];
        foreach ($entityarray as $newentity) {
            $entity = null;
            if (empty($newentity['id'])) {
                if (is_null($newentity['id'])) {
                    unset($newentity['id']);
                }
                $entity = new $entityclass(0);
                $entity->from_record((object) $newentity);
                $entity->create();
            } else {
                $entity = $entityclass::get_record(array('id' => $newentity['id']));
                $entity->from_record((object) $newentity);
            }
            $entity->save();
            $entities[] = $entity->to_record();
        }
        return $entities;
    }
}



