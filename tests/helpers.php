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
 * Test helpers
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function import_sample_fixture($cleanup = false) {
    global $CFG;
    $basepath = $CFG->dirroot . '/local/cveteval/tests/fixtures/';
    foreach (array(
        'evaluation_grid' => 'Sample_Evalgrid.csv',
        'situation' => 'Sample_Situations.csv',
        'planning' => 'Sample_Planning.csv',
        'grouping' => 'Sample_Grouping.csv'
    ) as $type => $filename) {
        $importclass = "\\local_cveteval\\local\\importer\\{$type}\\import";
        if (!class_exists($importclass)) {
            throw new moodle_exception('importclassnotfound', 'local_cveteval', null,
                ' class:' . $importclass);
        }
        if ($cleanup) {
            $importclass::cleanup();
        }
        $importclass::import($basepath . $filename);
    }
}

function inport_sample_users() {
    global $CFG;
    require_once($CFG->libdir.'/csvlib.class.php');
    require_once($CFG->dirroot.'/user/lib.php');
    $iid = csv_import_reader::get_new_iid('uploaduser');
    $cir = new csv_import_reader($iid, 'uploaduser');
    $content = file_get_contents( $CFG->dirroot . '/local/cveteval/tests/fixtures/users.csv');
    $cir->load_csv_content($content,'utf-8', 'comma');
    $cir->init();
    $columns = $cir->get_columns();
    while ($csvrow = $cir->next()) {
        $user = new stdClass();
        $user->auth = 'manual';
        $user->lang = $CFG->lang;
        $user->mnethostid = $CFG->mnet_localhost_id;
        foreach($csvrow as $key => $value) {
            $columnname = $columns[$key];
            $user->$columnname = trim($value);
        }
        if (! ($existinguser = core_user::get_user_by_username($user->username))) {
            user_create_user($user, true, false);
        } else {
            $user = (object) array_merge((array)$existinguser, (array) $user);
            user_update_user($user, true, false);
        }
    }
}
