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
 * Main page for all editions
 *
 * Routing is made through the action parameter.
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../config.php');
global $CFG;

use local_cltools\local\crud\helper\base as crud_helper;
use local_cltools\local\crud\helper\crud_list;

global $CFG, $OUTPUT, $PAGE;
require_login();
$PAGE->set_url('/local/cveteval/pages/index.php');
$PAGE->set_context(context_system::instance());

$innerlinks = array(
    'evaluation_grid' => '/local/cveteval/pages/evaluation_grid',
    'planning' => '/local/cveteval/pages/planning',
    'situation' => '/local/cveteval/pages/situation',
);

$innerlinkshtml = [];
foreach ($innerlinks as $name => $linkurl) {
    $linkhtml = html_writer::span(get_string("$name:entity", 'local_cveteval'));
    $linkhtml .= html_writer::link(
        new moodle_url($CFG->wwwroot . $linkurl), get_string('edit'));
    $innerlinkshtml[] = $linkhtml;
}
echo $OUTPUT->header();
echo html_writer::alist($innerlinkshtml);
echo $OUTPUT->footer();