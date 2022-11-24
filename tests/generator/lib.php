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
use local_cveteval\test\test_utils;

/**
 * Generator for local_cveteval.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_cveteval_generator extends \component_generator_base {

    /**
     * Callback
     *
     * Create dynamic entity
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments) {
        if (strpos($method, 'create_') === 0) {
            $entityname = str_replace('create_', '', $method);
            $classname = "\\local_cveteval\\local\\persistent\\{$entityname}\\entity";
            if (class_exists($classname)) {
                $data = array_shift($arguments);
                return $this->create_generic_entity($classname, $data);
            }
        }
        throw new BadMethodCallException();
    }

    /**
     * Create generic entity
     *
     * @param string $classname
     * @param array $data
     * @return mixed
     */
    private function create_generic_entity($classname, $data) {
        $entity = new $classname(0, (object) $data);
        $entity->create();
        return $entity;
    }

    /**
     * Create history
     *
     * @param array $data
     * @return mixed
     */
    public function create_history($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\history\entity::class, $data);
    }

    /**
     * Create group
     *
     * @param array $data
     * @return mixed
     */
    public function create_group($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\group\entity::class, $data);
    }

    /**
     * Create group assignment
     *
     * @param array $data
     * @return mixed
     */
    public function create_group_assignment($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\group_assignment\entity::class, $data);
    }

    /**
     * Create situation
     *
     * @param array $data
     * @return mixed
     */
    public function create_situation($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\situation\entity::class, $data);
    }

    /**
     * Create planning
     *
     * @param array $data
     * @return mixed
     */
    public function create_planning($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\planning\entity::class, $data);
    }


    /**
     * Create role
     *
     * @param array $data
     * @return mixed
     */
    public function create_role($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\role\entity::class, $data);
    }

    /**
     * Create appraisal
     *
     * @param array $data
     * @return mixed
     */
    public function create_appraisal($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\appraisal\entity::class, $data);
    }

    /**
     * Create appraisal criteria
     *
     * @param array $data
     * @return mixed
     */
    public function create_appraisal_criterion($data) {
        return $this->create_generic_entity(local_cveteval\local\persistent\appraisal_criterion\entity::class, $data);
    }
}
