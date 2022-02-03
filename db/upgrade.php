<?php
// This file is part of Moodle - https://moodle.org/
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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_cveteval
 * @category    upgrade
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\utils;

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_cveteval upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_cveteval_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2021033016) {
        utils::create_scale_if_not_present();
        upgrade_plugin_savepoint(true, 2021033016, 'local', 'cveteval');
    }
    if ($oldversion < 2021033034) {
        utils::setup_mobile_service(true);
        upgrade_plugin_savepoint(true, 2021033034, 'local', 'cveteval');
    }
    if ($oldversion < 2021092003) {
        utils::create_update_default_criteria_grid();
        upgrade_plugin_savepoint(true, 2021092003, 'local', 'cveteval');
    }
    if ($oldversion < 2021092006) {
        // Define field evalgridid to be added to local_cveteval_criterion.
        $table = new xmldb_table('local_cveteval_criterion');
        $field = new xmldb_field('evalgridid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'sort');

        // Conditionally launch add field evalgridid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Migrate existing data.
        foreach ($DB->get_records('local_cveteval_criterion') as $criterion) {
            $cevalgrid = $DB->get_records('local_cveteval_cevalgrid', ['criterionid' => $criterion->id]);
            foreach ($cevalgrid as $evalg) {
                $criterion->evalgridid = $evalg->evalgridid;
                $DB->update_record('local_cveteval_criterion', $criterion);
                $DB->delete_records('local_cveteval_cevalgrid', ['id' => $evalg->id]);
            }
        }
        // Change the current evaluation grid to the default one.
        $defaultgrid = $DB->get_record('local_cveteval_evalgrid', ['idnumber' => 'GRID01']);
        $defaultgrid->idnumber = \local_cveteval\local\persistent\evaluation_grid\entity::DEFAULT_GRID_SHORTNAME;
        $DB->update_record('local_cveteval_evalgrid', $defaultgrid);
        // Cveteval savepoint reached.
        upgrade_plugin_savepoint(true, 2021092006, 'local', 'cveteval');
    }
    if ($oldversion < 2021092009) {
        // Define table local_cveteval_history to be created.
        $table = new xmldb_table('local_cveteval_history');

        // Adding fields to table local_cveteval_history.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('comments', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('isactive', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_cveteval_history.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Adding indexes to table local_cveteval_history.
        $table->add_index('idnumber_idx', XMLDB_INDEX_UNIQUE, ['idnumber']);

        // Conditionally launch create table for local_cveteval_history.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Define table local_cveteval_history_mdl to be created.
        $table = new xmldb_table('local_cveteval_history_mdl');

        // Adding fields to table local_cveteval_history_mdl.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('tablename', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('tableid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('historyid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_cveteval_history_mdl.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for local_cveteval_history_mdl.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        utils::migrate_current_entity_to_history();
        utils::create_update_default_criteria_grid();
        // Cveteval savepoint reached.
        upgrade_plugin_savepoint(true, 2021092009, 'local', 'cveteval');
    }

    return true;
}
