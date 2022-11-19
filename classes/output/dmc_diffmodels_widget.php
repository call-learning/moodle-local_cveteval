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
namespace local_cveteval\output;

use local_cveteval\local\datamigration\data_migration_utils;
use local_cveteval\local\persistent\history;
use local_cveteval\output\helpers\output_helper;
use pix_icon;
use renderer_base;
use stdClass;

/**
 * Renderable for datamigration controller
 *
 * @package    local_cveteval
 * @copyright  2020 CALL Learning - Laurent David laurent@call-learning.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dmc_diffmodels_widget extends dmc_entity_renderer_base {

    /**
     * Export for template
     *
     * @param renderer_base $output
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $output) {
        $context = parent::export_for_template($output);
        $stepdata = $this->dmc->get_step_data();
        history\entity::disable_history();
        $context->entitieswithcontext =
                $this->get_entity_step_by_context(self::ALL_CONTEXTS, $stepdata, $output);
        return $context;
    }

    /**
     * Get entity step by context
     *
     * @param array $contexts
     * @param object $stepdata
     * @param renderer_base $output
     * @return array
     */
    protected function get_entity_step_by_context($contexts, $stepdata, $output) {
        $entitiescontext = [];
        foreach ($contexts as $context) {
            foreach ($stepdata->$context as $entityclass => $matchs) {
                $baseclassname = data_migration_utils::get_base_class($entityclass);
                $currententity = $entitiescontext[$baseclassname] ?? null;
                if (empty($currententity)) {
                    $currententity = new stdClass();
                    $currententity->entityname = get_string("$baseclassname:entity", 'local_cveteval');
                    $currententity->entitytype = $baseclassname;
                    $currententity->contexts = array_fill_keys($contexts, null);
                }
                if (empty($currententity->contexts[$context])) {
                    $contextinfo = new stdClass();
                    $contextinfo->contextname = get_string("dmc:$context", "local_cveteval");
                    $contextinfo->contexttype = $context;
                    $contextinfo->contextstatus = $context == 'matchedentities' ? 'matched' : 'unmatched';
                    $icon = new pix_icon($contextinfo->contextstatus == 'matched' ? 'e/tick' : 'i/ne_red_mark',
                            get_string('dmc:' . $contextinfo->contextstatus, 'local_cveteval'));
                    $contextinfo->contextstatusicon = $output->render($icon);
                    $contextinfo->contextstatusclass = ($context == 'matchedentities') ? 'success' : 'warning';
                    $contextinfo->entities = [];
                    $contextinfo->entitiescount = 0;
                    $currententity->contexts[$context] = $contextinfo;
                }
                foreach ($matchs as $entityoriginid => $entitydestid) {
                    $currententity->contexts[$context]->entities[] =
                            output_helper::output_entity_info($entitydestid ?: $entityoriginid, $baseclassname);
                    $currententity->contexts[$context]->entitiescount = count($currententity->contexts[$context]->entities);
                }
                $entitiescontext[$baseclassname] = $currententity;
            }
        }
        // We remove the key that are now not used and can confuse the mustache template (object vs array).
        $entitiescontext = array_values($entitiescontext);
        foreach ($entitiescontext as $entitycontext) {
            $entitycontext->contexts = array_values($entitycontext->contexts);
        }
        return array_values($entitiescontext);
    }
}
