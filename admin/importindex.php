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
 * History of import management
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cltools\output\table\entity_table_renderable;

require_once(__DIR__ . '../../../../config.php');
global $CFG, $OUTPUT, $PAGE;
require_once($CFG->libdir . "/adminlib.php");
admin_externalpage_setup('cvetevalimportindex');
require_capability('local/cveteval:manageimport', context_system::instance());
$returnurl = optional_param('returnurl', null, PARAM_RAW);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('import:list', 'local_cveteval'));
$PAGE->set_heading(get_string('import:list', 'local_cveteval'));
$PAGE->set_url(new moodle_url('/local/cveteval/admin/importindex.php'));
/* @var core_renderer $OUTPUT . */

if ($returnurl) {
    $PAGE->set_button(
            $OUTPUT->single_button(new moodle_url($returnurl),
                    get_string('continue'))
    );
}
echo $OUTPUT->header();

$entitylist = new local_cveteval\local\persistent\history\table();

$renderable = new entity_table_renderable($entitylist);

$renderer = $PAGE->get_renderer('local_cltools');
echo $renderer->render($renderable);

echo $OUTPUT->footer();
