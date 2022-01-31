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

namespace local_cveteval\local\persistent\history;

use cache;
use core\persistent;
use local_cltools\local\crud\enhanced_persistent;
use local_cltools\local\crud\enhanced_persistent_impl;
use local_cltools\local\field\blank_field;
use local_cltools\local\field\boolean;
use local_cltools\local\field\text;

defined('MOODLE_INTERNAL') || die();

/**
 * Import and model history
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class entity extends persistent implements enhanced_persistent {

    use enhanced_persistent_impl;

    const TABLE = 'local_cveteval_history';

    const CACHE_REQUEST_CURRENT_HISTORY_ID_NAME = 'local_cveteval_currenthistoryid';
    const CACHE_REQUEST_CURRENT_HISTORY_STRICT_NAME = 'local_cveteval_currenthistory_is_strict';

    const HISTORY_DISABLED_CONFIG_NAME = 'history_disabled_globally';

    /**
     * Define fields
     *
     * @return array
     */
    public static function define_fields(): array {
        return [
                new text('idnumber'),
                new text('comments'),
                new boolean(['fieldname' => 'isactive', 'editable' => true]),
                new blank_field(['fieldname' => 'tools', 'fullname' => get_string('history:tools', 'local_cveteval')]),
        ];
    }

    /**
     * Get current
     *
     * @return false|entity
     */
    public static function get_current() {
        static $current = null;
        // Prevent unnecessary loading of the history entity.
        $currentid = static::get_current_id();
        if (!$currentid) {
            $current = null;
        } else if ((empty($current) || $current->get('id') != $currentid)) {
            $current = self::get_record(['id' => $currentid]);
        }
        return $current;
    }

    /**
     * Get current identifier
     *
     * @return false|int
     */
    public static function get_current_id() {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        $activeid = $cache->get(self::CACHE_REQUEST_CURRENT_HISTORY_ID_NAME);
        return !empty($activeid) ? $activeid : 0;
    }

    /**
     * Is the current id a strict or lax research
     *
     * If is a strict lookup, then we just look for the current id if not (most of the case)
     * we look for the current history id + history id =0
     *
     * @return false|int
     */
    public static function is_currentid_strict() {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        $strict = $cache->get(self::CACHE_REQUEST_CURRENT_HISTORY_STRICT_NAME);
        return $strict;
    }

    /**
     * Set current active identifier
     *
     * This will be valid for the current request scope
     *
     * @param int $id import id
     * @param bool $strict should only look for this historyid or also the default historyid (0)
     * @return false|int
     * @throws \moodle_exception
     */
    public static function set_current_id($id, $strict = false) {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        if (!$id) {
            if (!self::record_exists($id)) {
                throw new \moodle_exception('couldnotfindhistory', 'local_cveteval', '', $id);
            }
        }
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_ID_NAME, $id);
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_STRICT_NAME, $strict);
    }

    /**
     * Disable history for current request
     */
    public static function reset_current_id() {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_ID_NAME, 0);
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_STRICT_NAME, false);
    }

    const HISTORY_DISABLED_ID = -1;

    /**
     * Disable history for current request
     */
    public static function disable_history() {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_ID_NAME, self::HISTORY_DISABLED_ID);
        $cache->set(self::CACHE_REQUEST_CURRENT_HISTORY_STRICT_NAME, false);
    }

    /**
     * @return false|entity
     */
    public static function is_disabled() {
        $cache = cache::make('local_cveteval', 'persistenthistory');
        if (get_config('local_cveteval', self::HISTORY_DISABLED_CONFIG_NAME)) {
            return true;
        }
        $activeid = $cache->get(self::CACHE_REQUEST_CURRENT_HISTORY_ID_NAME);
        return $activeid == self::HISTORY_DISABLED_ID;
    }

    public static function disable_history_globally() {
        set_config(self::HISTORY_DISABLED_CONFIG_NAME, true, 'local_cveteval');
    }
}
