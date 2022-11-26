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
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat custom steps and configuration for local_cveteval.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_cveteval extends behat_base {
    /**
     * Set the default history for all subsequent navigation
     *
     * @Given /^I set CompetVetEval default history to "([^"]*)"$/
     * @param int $historyidnumber
     */
    public function i_set_competveteval_default_history_to($historyidnumber): void {
        $history = local_cveteval\local\persistent\history\entity::get_record(['idnumber' => $historyidnumber]);
        $history::set_current_id($history->get('id'));
    }

    /**
     * Convert page names to URLs for steps like 'When I am on the "[identifier]" "[page type]" page'.
     *
     * A typical example might be:
     *     When I am on the "Test quiz" "mod_quiz > Responses report" page
     * which would cause this method in behat_mod_quiz to be called with
     * arguments 'Responses report', 'Test quiz'.
     *
     * You should override this as appropriate for your plugin. The method
     * {@see behat_navigation::resolve_core_page_instance_url()} is a good example.
     *
     * Your overridden method should document the recognised page types with
     * a table like this:
     *
     * Recognised page names are:
     * | Type      | identifier meaning | Description                                     |
     *
     * @param string $type identifies which type of page this is, e.g. 'Attempt review'.
     * @param string $identifier identifies the particular page, e.g. 'Test quiz > student > Attempt 1'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        global $CFG;
        require_once($CFG->dirroot . '/local/cltools/tests/lib.php');
        switch ($type) {
            case 'Manage Model':
                $entity = \local_cveteval\local\persistent\history\entity::get_record(['idnumber' => $identifier]);
                return new moodle_url("/local/cveteval/manage/index.php", ['importid' => $entity->get('id')]);
        }
        throw new Exception('Unrecognised page type "' . $type . '."');
    }

    /**
     * Convert page names to URLs for steps like 'When I am on the "[page name]" page'.
     *
     * You should override this as appropriate for your plugin. The method
     * {@see behat_navigation::resolve_core_page_url()} is a good example.
     *
     * Your overridden method should document the recognised page types with
     * a table like this:
     *
     * Recognised page names are:
     * | Page            | Description                                                    |
     *
     * @param string $page name of the page, with the component name removed e.g. 'Admin notification'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_url(string $page): moodle_url {
        switch ($page) {
            case 'Evaluation':
                return new moodle_url("/local/cveteval/pages/assessment/mysituations.php");
        }
        throw new Exception('Unrecognised page type "' . $page . '."');
    }

}
