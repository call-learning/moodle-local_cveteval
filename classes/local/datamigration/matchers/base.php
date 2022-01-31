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

namespace local_cveteval\local\datamigration\matchers;

use core\persistent;
use local_cveteval\local\datamigration\data_model_matcher;
use local_cveteval\local\persistent\history\entity as history_entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Entity matcher.
 *
 * Will attempt to match a given entity in two different histories.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base {

    protected $matchedentities = [];
    protected $unmatchedentities = [];
    protected $orphanedentities = [];
    /**
     * Data migration controller
     *
     * @var data_model_matcher
     */
    protected data_model_matcher $dm;

    public function __construct(data_model_matcher $dm) {
        $this->dm = $dm;
        $this->prepare_entities();
    }

    /**
     * Prepare and divide entities into two categories
     *
     * * The entities that have been matched
     * * The other ones
     *
     * @throws \moodle_exception
     */
    protected function prepare_entities() {
        $entityclass = static::get_entity();
        history_entity::set_current_id($this->dm->get_origin_id());
        $oldentities = $entityclass::get_records();
        $oldentitiesid = array_map(
            function($e) {
                return $e->get('id');
            }, $oldentities);

        history_entity::set_current_id($this->dm->get_dest_id());
        $newentities = $entityclass::get_records();
        if (!$newentities) {
            $newentities = [];
        }

        $this->orphanedentities = array_fill_keys($oldentitiesid, 0);
        foreach ($newentities as $entity) {
            $oldentities = $this->match($entity);
            $id = $entity->get('id');
            if (empty($oldentities) || count($oldentities) > 1) {
                $this->unmatchedentities[] = $id;
            } else {
                $oldentity = reset($oldentities);
                $oldentityid = $oldentity->get('id');
                $this->matchedentities[$oldentityid] = $id;
                if(isset($this->orphanedentities[$oldentityid])) {
                    unset($this->orphanedentities[$oldentityid]);
                }
            }
        }
    }

    /**
     * Retrieve a list of matched entities' id (which are both in old and new model)
     *
     *
     * @return int[]|false associative array with oldentityid => newentityid
     */
    public function get_matched_origin_entities() {
        return $this->matchedentities;
    }

    /**
     * Retrieve a list of unmatched entities'  id (which are only in the new model)
     *
     * @return int[]|false array composed of unmatched entity id
     */
    public function get_unmatched_dest_entities() {
        return $this->unmatchedentities;
    }

    /**
     * Retrieve a list of orphaned entities'  id (which are only in the old model)
     *
     * @return int[]|false array composed of orphaned entity id
     */
    public function get_orphaned_origin_entities() {
        return $this->orphanedentities;
    }

    /**
     * Try to match a given model/entity type
     *
     * @return persistent[]|false
     */
    protected function match(persistent $newentity) {
        $activeid = history_entity::get_current_id();
        history_entity::set_current_id($this->dm->get_origin_id());
        $entities = $this->do_match($newentity);
        if ($activeid) {
            history_entity::set_current_id($activeid);
        } else {
            history_entity::reset_current_id();
        }
        return $entities;
    }

    /**
     * Internal: Try to match a given model/entity type
     *
     * The current active history is the origin
     *
     * @return persistent|persistent[]|false
     */
    abstract protected function do_match(persistent $newentity);

    /**
     * Related entity class
     *
     * @return mixed
     */
    abstract static public function get_entity();

    protected function get_entity_field_name($entityid, $historyid, $field, $entityclass) {
        global $DB;
        $entitysql = $entityclass::get_historical_sql_query_for_id("e", $historyid);
        return $DB->get_field_sql("SELECT e.$field FROM $entitysql WHERE id = :id", ['id' => $entityid]);
    }
}