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

namespace local_cveteval\local\datamigration;

/**
 * Data migration entity
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_model_matcher {
    protected array $matchers;
    private $originimportid;
    private $destimportid;

    /**
     * Constructor
     *
     * @param int $originimportid
     * @param int $destimportid
     */
    public function __construct(int $originimportid, int $destimportid) {
        $this->originimportid = $originimportid;
        $this->destimportid = $destimportid;
        $this->matchers = [];
        foreach (self::get_model_matchers_class() as $macherclass) {
            $this->matchers[] = new $macherclass($this);
        }
    }

    /**
     * Get all entities matcher
     *
     * @return array
     */
    public static function get_model_matchers_class() {
        $files = scandir(__DIR__ . '/matchers');
        $classes = array_filter($files, function($file) {
            return !is_dir(__DIR__ . "/$file") && !in_array($file, ['.', '..']);
        });
        $classes = array_map(function($cl) {
            return '\local_cveteval\local\datamigration\matchers\\' . basename($cl, '.php');
        }, $classes);
        return array_filter($classes, function($class) {
            return class_exists($class) && in_array(matchers\base::class, class_parents($class));
        });
    }

    /**
     * Get origin id
     *
     * @return int
     */
    public function get_origin_id() {
        return $this->originimportid;
    }

    /**
     * Get dest id
     *
     * @return int
     */
    public function get_dest_id() {
        return $this->destimportid;
    }

    /**
     * Return an associative array of entities that have been matched from the dest to origin type of models
     *
     * @returns array associative array of class/entity id vs the matched old entity id
     */
    public function get_matched_entities_list() {
        return $this->iterate_over_matchers("get_matched_origin_entities");
    }

    /**
     * Helper function that iterates through all matchers
     *
     * @param $callbackname
     * @return array
     */
    protected function iterate_over_matchers($callbackname) {
        $entities = [];
        foreach ($this->matchers as $matcher) {
            $entityclass = $matcher->get_entity();
            if (empty($entities[$entityclass])) {
                $entities[$entityclass] = [];
            }
            $entities[$entityclass] = $matcher->$callbackname();
        }
        return $entities;
    }

    /**
     * Return an array of entities ID that could not be matched from the dest to origin models
     *
     * @return array array of class/entity id that could not be matched
     */
    public function get_unmatched_entities_list() {
        return $this->iterate_over_matchers("get_unmatched_dest_entities");
    }

    /**
     * Return an array of entities ID that could not be in the origin model from the dest
     *
     * @return array array of class/entity id that could not be matched
     */
    public function get_orphaned_entities_list() {
        return $this->iterate_over_matchers("get_orphaned_origin_entities");
    }
}
