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

namespace local_cveteval\local\persistent;

use Closure;
use dml_exception;
use stdClass;

/**
 * Model with history implementation
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait model_with_history_impl {
    /**
     * Load a list of records.
     *
     * Take into account only active records or current history.
     *
     * @param array $filters Filters to apply.
     * @param string $sort Field to sort by.
     * @param string $order Sort order.
     * @param int $skip Limitstart.
     * @param int $limit Number of rows to return.
     *
     * @return static[]
     */
    public static function get_records($filters = array(), $sort = '', $order = 'ASC', $skip = 0, $limit = 0) {
        global $DB;
        $orderby = '';
        if (!empty($sort)) {
            $orderby = " ORDER BY $sort $order";
        }
        [$sql, $where, $params] = self::prepare_sql_get_records($filters);
        if ($where) {
            $where = "WHERE $where";
        }
        $records = $DB->get_records_sql("SELECT e.* FROM $sql $where $orderby", $params, $skip, $limit);
        $instances = array();

        if ($records) {
            $instances = array_map(function($r) {
                return new static(0, $r);
            }, $records);
        }
        return $instances;
    }

    /**
     * Prepare for get record(s)
     *
     * @param array $filters
     * @param string $alias
     * @return array
     */
    protected static function prepare_sql_get_records($filters, $alias = "e") {
        [$where, $params] = self::where_clause(static::TABLE, $alias, $filters);
        $sql = self::get_historical_sql_query($alias);
        return [$sql, $where, $params];
    }

    /**
     * This is a copy of the DB where clause routine that is protected so not accessible
     * unfortunately
     *
     * Returns the SQL WHERE conditions.
     *
     * @param string $table The table name that these conditions will be validated against.
     * @param string $alias alias for the the table/entity
     * @param array|null $conditions The conditions to build the where clause. (must not contain numeric indexes)
     * @return array An array list containing sql 'where' part and 'params'.
     */
    protected static function where_clause($table, $alias, array $conditions = null) {
        global $DB;

        $whereclause = function($table, $alias, array $conditions = null) {
            //phpcs:disable Squiz.Scope.StaticThisUsage.Found
            // We accept nulls in conditions.
            $conditions = is_null($conditions) ? array() : $conditions;

            if (empty($conditions)) {
                return array('', array());
            }

            // Some checks performed under debugging only.
            if (debugging()) {
                $columns = $this->get_columns($table);
                if (empty($columns)) {
                    // No supported columns means most probably table does not exist.
                    throw new dml_exception('ddltablenotexist', $table);
                }
                foreach ($conditions as $key => $value) {
                    if (!isset($columns[$key])) {
                        $a = new stdClass();
                        $a->fieldname = $key;
                        $a->tablename = $table;
                        throw new dml_exception('ddlfieldnotexist', $a);
                    }
                    $column = $columns[$key];
                    if ($column->meta_type == 'X') {
                        // Ok so the column is a text column. sorry no text columns in the where clause conditions.
                        throw new dml_exception('textconditionsnotallowed', $conditions);
                    }
                }
            }
            $allowedtypes = $this->allowed_param_types();
            $where = array();
            $params = array();

            foreach ($conditions as $key => $value) {
                if (is_int($key)) {
                    throw new dml_exception('invalidnumkey');
                }
                if (is_null($value)) {
                    $where[] = "$alias.$key IS NULL";
                } else {
                    if ($allowedtypes & SQL_PARAMS_NAMED) {
                        // Need to verify key names because they can contain, originally,
                        // spaces and other forbidden chars when using sql_xxx() functions and friends.
                        $normkey = trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $key), '-_');
                        if ($normkey !== $key) {
                            debugging('Invalid key found in the conditions array.');
                        }
                        $where[] = "$alias.$key = :$normkey";
                        $params[$normkey] = $value;
                    } else {
                        $where[] = "$alias.$key = ?";
                        $params[] = $value;
                    }
                }
            }
            $where = implode(" AND ", $where);
            return array($where, $params);
            //phpcs:enable Squiz.Scope.StaticThisUsage.Found
        };
        $whereclosure = Closure::bind($whereclause, $DB, get_class($DB));
        return $whereclosure($table, $alias, $conditions);
    }

    /**
     * Get historical query for specific current id.
     *
     * @param string $alias
     * @return string
     */
    public static function get_historical_sql_query($alias = "e") {
        if (history\entity::is_disabled()) {
            $currentid = history\entity::HISTORY_DISABLED_ID;
        } else {
            // Returns 0 if no active ID.
            $currentid = history\entity::get_current_id();
        }
        return static::get_historical_sql_query_for_id($alias, $currentid);
    }

    /**
     * Get historical query for specific history id
     *
     * @param string $alias
     * @param int $historyid if 0, all active history id
     * @return string
     */
    public static function get_historical_sql_query_for_id($alias = "e", $historyid = 0) {
        $currenttable = static::TABLE;
        if ($historyid == history\entity::HISTORY_DISABLED_ID) {
            return "{" . $currenttable . "} AS $alias";
        }
        $defaulthistorysql = "OR hmtable.historyid = 0";

        if (empty($historyid)) {
            // Mode = all active history ?
            $historytable = history\entity::TABLE;
            $currentidquery =
                    "hmtable.historyid IN (SELECT id FROM {" . $historytable . "} h WHERE h.isactive = 1) $defaulthistorysql";
        } else {
            // Mode  = currentid only.
            $currentidquery = "hmtable.historyid = $historyid";
            if (!history\entity::is_currentid_strict()) {
                $currentidquery .= " $defaulthistorysql";
            }
        }

        $historymtable = history_model\entity::TABLE;
        return "(SELECT ctable.*
                FROM {" . $currenttable . "} ctable
                LEFT JOIN {" . $historymtable . "} AS hmtable
                ON hmtable.tablename = '$currenttable' AND ctable.id = hmtable.tableid AND ($currentidquery)
                WHERE hmtable.id IS NOT NULL) AS $alias";
    }

    /**
     * Load a single record.
     *
     * Take into account only active records or current history.
     *
     * @param array $filters Filters to apply.
     * @return false|static
     */
    public static function get_record($filters = array()) {
        global $DB;
        [$sql, $where, $params] = self::prepare_sql_get_records($filters);
        if ($where) {
            $where = "WHERE $where";
        }
        $record = $DB->get_record_sql("SELECT e.* FROM $sql $where", $params);
        return $record ? new static(0, $record) : false;
    }

    /**
     * Load a list of records based on a select query.
     *
     * Take into account only active records or current history.
     *
     * @param string $select
     * @param array $params
     * @param string $sort
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     * @return static[]
     */
    public static function get_records_select($select, $params = null, $sort = '', $fields = '*', $limitfrom = 0, $limitnum = 0) {
        global $DB;
        $alias = "e";
        $sql = self::get_historical_sql_query($alias);
        $fields = "e.*";
        $records = $DB->get_records_sql("SELECT $fields FROM $sql WHERE $select $sort", $params, $limitfrom, $limitnum);
        $instances = array();
        if ($records) {
            $instances = array_map(function($r) {
                return new static(0, $r);
            }, $records);
        }
        return $instances;
    }

    /**
     * Count a list of records.
     *
     * Take into account only active records or current history.
     *
     * @param array $conditions An array of conditions.
     * @return int
     */
    public static function count_records(array $conditions = array()) {
        [$sql, $where, $params] = self::prepare_sql_get_records($conditions);
        return self::do_count_records_select($sql, $where, $params);
    }

    /**
     * Count the records in a table which match a particular WHERE clause.
     *
     * @param string $sql The query with potential historical joins
     * @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
     * @param array|null $params array of sql parameters
     * @param string $countitem The count string to be used in the SQL call. Default is COUNT('x').
     * @return int The count of records returned from the specified criteria.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    protected static function do_count_records_select($sql, $select, array $params = null, $countitem = "COUNT('x')") {
        global $DB;
        if ($select) {
            $select = "WHERE $select";
        }
        return $DB->count_records_sql("SELECT $countitem FROM $sql $select", $params);
    }

    /**
     * Count a list of records.
     *
     * Take into account only active records or current history.
     *
     * @param string $select
     * @param array $params
     * @return int
     */
    public static function count_records_select($select, $params = null) {
        $sql = self::get_historical_sql_query();
        return self::do_count_records_select($sql, $select, $params);
    }

    /**
     * Check if a record exists by ID.
     *
     * Take into account only active records or current history.
     *
     * @param int $id Record ID.
     * @return bool
     */
    public static function record_exists($id) {
        $sql = self::get_historical_sql_query();
        return self::do_record_exists_select($sql, "id =:id", ['id' => $id]);
    }

    /**
     * Test whether any records exists in a table which match a particular WHERE clause.
     *
     * @param string $sql The query with potential historical joins
     * @param string $select A fragment of SQL to be used in a WHERE clause in the SQL call.
     * @param array|null $params array of sql parameters
     * @return bool true if a matching record exists, else false.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    protected static function do_record_exists_select($sql, $select, array $params = null) {
        global $DB;
        if ($select) {
            $select = "WHERE $select";
        }
        return $DB->record_exists_sql("SELECT 'x' FROM $sql $select", $params);
    }

    /**
     * Check if a records exists.
     *
     * Take into account only active records or current history.
     *
     * @param string $select
     * @param array|null $params
     * @return bool
     * @throws dml_exception
     */
    public static function record_exists_select($select, array $params = null) {
        $sql = self::get_historical_sql_query();
        return self::do_record_exists_select($sql, $select, $params);
    }

    /**
     * Hook to execute after a create.
     *
     * This is only intended to be used by child classes, do not put any logic here!
     *
     * @return void
     */
    protected function after_create() {
        $id = $this->raw_get('id');
        if (!history\entity::is_disabled()) {
            $historyid = history\entity::get_current_id();
            $historymodel = new history_model\entity(0, (object) [
                    'tablename' => static::TABLE,
                    'tableid' => $id,
                    'historyid' => $historyid
            ]);
            $historymodel->create(); // Use cache maybe ?
        }
    }

    /**
     * Hook to execute after a delete.
     *
     * @param bool $result Whether or not the delete was successful.
     * @return void
     */
    protected function after_delete($result) {
        if ($result) {
            $id = $this->raw_get('id');
            foreach (history_model\entity::get_records(['tablename' => static::TABLE, 'tableid' => $id]) as $historyrecord) {
                $historyrecord->delete();
            }
        }
    }
}
