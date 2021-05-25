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
 * Assessment Page
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cltools\local\crud\entity_exporter;
use local_cltools\local\filter\basic_filterset;
use local_cltools\local\filter\filter;
use local_cltools\output\table\entity_table_renderable;
use local_cveteval\local\assessment\mystudents;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\exporter;
use local_cveteval\local\utils;

require_once(__DIR__ . '/../../../../config.php');
global $CFG, $OUTPUT, $PAGE, $USER;
$situationid = required_param('situationid', PARAM_INT);
require_login();
if (utils::get_user_role_id($USER->id) != role_entity::ROLE_ASSESSOR_ID) {
    print_error('cannotaccess', 'local_cveteval');
}
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('assessment', 'local_cveteval')
    . ':'
    . get_string('mystudents', 'local_cveteval'));
$PAGE->set_heading(get_string('mystudents', 'local_cveteval'));
$currenturl = new moodle_url('/local/cveteval/pages/assessment/mystudents.php', array(
    'situationid' => $situationid,
));
$PAGE->set_url($currenturl);

$situationnode = $PAGE->navigation->add(
    get_string('mysituations', 'local_cveteval'),
    new moodle_url('/local/cveteval/pages/assessment/mysituations.php'),
    navigation_node::TYPE_CONTAINER);
$currentnode = $situationnode->add(
    get_string('mystudents', 'local_cveteval'),
    $currenturl);
$currentnode->make_active();

echo $OUTPUT->header();

$situation = new local_cveteval\local\persistent\situation\entity($situationid);
$situationcontext = new exporter($situation);

echo $OUTPUT->render_from_template(
    "local_cveteval/situation_simple",
    $situationcontext->export($OUTPUT)
);

$uniqueid = html_writer::random_id('situationtable');
$entitylist = new mystudents($uniqueid);
$filterset = new basic_filterset(
    [
        'situationid' => (object)
        [
            'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
            'required' => true
        ],
        'roletype' => (object)
        [
            'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
            'required' => true
        ],
        'appraiserid' => (object)
        [
            'filterclass' => 'local_cltools\\local\filter\\numeric_comparison_filter',
            'required' => true
        ],
    ]
);
$filterset->set_join_type(filter::JOINTYPE_ALL);
$filterset->add_filter_from_params(
    'situationid', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object) ['direction' => '=', 'value' => $situationid])]
);
$filterset->add_filter_from_params(
    'roletype', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object) ['direction' => '=', 'value' => role_entity::ROLE_ASSESSOR_ID])]
);
$filterset->add_filter_from_params(
    'appraiserid', // Field name.
    filter::JOINTYPE_ALL,
    [json_encode((object) ['direction' => '=', 'value' => $USER->id])]
);
$entitylist->set_extended_filterset($filterset);

$renderable = new entity_table_renderable($entitylist);

$renderer = $PAGE->get_renderer('local_cltools');
/* @var entity_table_renderable entity table */
echo $renderer->render($renderable);

echo $OUTPUT->footer();