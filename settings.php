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
 * You may have settings in your plugin
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $CFG;

    $enabled = !empty($CFG->enablecompetveteval) && $CFG->enablecompetveteval;

    $settings = new admin_category('cveteval', get_string('pluginname', 'local_cveteval'));

    $generalsettings = new admin_settingpage(
        'competveteval_general', get_string('settings:general', 'local_cveteval'),
        'local/cveteval:manageevaluationtemplate',
        !$enabled);
    $settings->add('cveteval', $generalsettings);

    $scales = grade_scale::fetch_all_global();
    $scalelist = array_map(function($sc) {
        return $sc->get_name();
    }, $scales);

    if (empty($scalelist)) {
        $generalsettings->add(
            new admin_setting_configempty('local_cveteval/grade_scale',
                new lang_string('settings:grade_scale', 'local_cveteval'),
                new lang_string('settings:grade_scale', 'local_cveteval')
            )
        );
    } else {
        $defaultid = array_search(get_string('grade:defaultscale', 'local_cveteval'), $scalelist);
        $generalsettings->add(
            new admin_setting_configselect('local_cveteval/grade_scale',
                new lang_string('settings:grade_scale', 'local_cveteval'),
                new lang_string('settings:grade_scale', 'local_cveteval'),
                $defaultid ?: array_keys($scalelist)[0],
                $scalelist
            )
        );
    }

    $settings->add('cveteval',
        new admin_externalpage(
            'cvetevalimport',
            get_string('import:new', 'local_cveteval'),
            $CFG->wwwroot . '/local/cveteval/admin/import.php',
            array('local/cveteval:manageimport'),
            !$enabled)
    );

    $settings->add('cveteval',
        new admin_externalpage(
            'cvetevalimportindex',
            get_string('import:listall', 'local_cveteval'),
            $CFG->wwwroot . '/local/cveteval/admin/importindex.php',
            array('local/cveteval:manageimport'),
            !$enabled)
    );


    $settings->add('cveteval',
        new admin_externalpage(
            'cvetevalcleanupmodel',
            get_string('cleanup:model', 'local_cveteval'),
            $CFG->wwwroot . '/local/cveteval/admin/cleanup.php?type=model',
            array('local/cveteval:cleanupdata'),
            !$enabled)
    );

    $settings->add('cveteval',
        new admin_externalpage(
            'cvetevalcleanupuserdata',
            get_string('cleanup:userdata', 'local_cveteval'),
            $CFG->wwwroot . '/local/cveteval/admin/cleanup.php?type=userdata',
            array('local/cveteval:cleanupdata'),
            !$enabled)
    );

    $settings->add('cveteval',
        new admin_externalpage(
            'cvetevalmigration',
            get_string('datamigration', 'local_cveteval'),
            $CFG->wwwroot . '/local/cveteval/admin/datamigration/index.php',
            array('local/cveteval:datamigration'),
            !$enabled)
    );

    if ($enabled) {
        $ADMIN->add('localplugins', $settings); // Add it to the main admin menu.
        $ADMIN->add('root', new admin_externalpage('cvetevalmenu',
                get_string('pluginname', 'local_cveteval'),
                new moodle_url('/admin/category.php', ['category' => 'cveteval'])
        )); // Add a link in the root menu.
    }
    // Create a global Advanced Feature Toggle.
    $enableoption = new admin_setting_configcheckbox('enablecompetveteval',
        new lang_string('enablecompetveteval', 'local_cveteval'),
        new lang_string('enablecompetveteval', 'local_cveteval'),
        1);
    $enableoption->set_updatedcallback('local_cveteval_enable_disable_plugin_callback');
    $optionalsubsystems = $ADMIN->locate('optionalsubsystems');
    $optionalsubsystems->add($enableoption);
}
