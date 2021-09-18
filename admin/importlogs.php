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
 * Import list or start new import Page
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cltools\local\filter\basic_filterset;
use local_cltools\local\filter\filter;
use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\assessment\assessment_situation;

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_login();
require_capability('local/cveteval:import', context_system::instance());
$importid = required_param('importid', PARAM_INT);
$returnurl = optional_param('returnurl', null, PARAM_RAW);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('import:logs', 'local_cveteval'));
$PAGE->set_heading(get_string('import:logs', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/pages/import.php'));
/* @var core_renderer $OUTPUT */

if ($returnurl) {
    $PAGE->set_button(
        $OUTPUT->single_button(new moodle_url($returnurl),
            get_string('continue'))
    );
}
echo $OUTPUT->header();

$tableuniqueid = html_writer::random_id('importtable');
$entitylist = new local_cveteval\local\persistent\import_log\table($tableuniqueid);

$filterset = new basic_filterset(
    [
        'importid' => (object)
        [
            'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
            'required' => true
        ],
        'module' => (object)
        [
            'filterclass' => 'local_cltools\\local\filter\\string_filter',
            'required' => true
        ],
    ]
);
$filterset->set_join_type(filter::JOINTYPE_ALL);
$filterset->add_filter_from_params(
    'importid', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object) ['direction' => '=', 'value' => $importid])]
);
$filterset->add_filter_from_params(
    'module', // Field name.
    filter::JOINTYPE_ALL,
    ["local_envf"]
);
$entitylist->set_extended_filterset($filterset);

$renderable = new entity_table_renderable($entitylist);

$renderer = $PAGE->get_renderer('local_cltools');
echo $renderer->render($renderable);

echo $OUTPUT->footer();
