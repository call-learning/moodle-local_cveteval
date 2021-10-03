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

namespace local_cveteval\local\datamigration;

use cache;
use core\notification;
use core_php_time_limit;

defined('MOODLE_INTERNAL') || die();

/**
 * Data migration controller
 *
 * @package   local_cveteval
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_migration_controller {
    protected $currentstep = 0;

    const INIT_STEP = 0;
    const CHOOSE_HISTORY_STEP = 1;
    const DIFF_MODEL_STEP = 2;
    const USER_DATA_MIGRATION = 3;

    const STEPS = [
            'init',
            'choosehistory',
            'diffmodels',
            'diffmodelsmodifications',
            'userdatamigration',
            'final'
    ];

    const CACHE_SESSION_MIGRATION_VAR_ID = 'cveteval_migration';
    const CACHE_DATA_MIGRATION_NAME = 'datamigration';

    public function __construct($stepname) {
        $stepnametoid = array_flip(self::STEPS);
        $this->currentstep = $stepnametoid[trim(strtolower($stepname))] ?? 0;
        if ($this->currentstep == 0) {
            $this->reset_step_data();
        }
    }

    public function get_step() {
        return self::STEPS[$this->currentstep];
    }

    public function set_step_data($data) {
        $data = is_object($data) ? (array) $data : $data;
        if (!empty($data)) {
            $migrationdata = $this->get_step_data();
            if (!empty($migrationdata)) {
                $migrationdata = array_replace_recursive((array) $migrationdata, (array) $data);
            } else {
                $migrationdata = (array) $data;
            }
            $this->raw_set_step_data($migrationdata);
        }
    }

    public function reset_step_data() {
        $cache = cache::make('local_cveteval', self::CACHE_DATA_MIGRATION_NAME);
        $cache->purge_current_user();
    }

    public function get_step_data() {
        $cache = cache::make('local_cveteval', self::CACHE_DATA_MIGRATION_NAME);
        $data = $cache->get(self::CACHE_SESSION_MIGRATION_VAR_ID);
        return !empty($data) ? $data : new \stdClass();
    }

    private function raw_set_step_data($data) {
        $cache = cache::make('local_cveteval', self::CACHE_DATA_MIGRATION_NAME);
        $cache->set(self::CACHE_SESSION_MIGRATION_VAR_ID, (object) $data);
    }

    public function execute_process($renderer, $renderable, $form) {
        $callback = "process_" . self::STEPS[$this->currentstep];
        if (method_exists($this, $callback)) {
            return $this->$callback($renderer, $renderable, $form);
        } else {
            return $this->process_standard($renderer, $renderable, $form);
        }
    }

    public function prepare_page() {
        global $PAGE;
        $PAGE->set_cacheable(false);    // Progress bar might be used here.
        core_php_time_limit::raise(HOURSECS);
        raise_memory_limit(MEMORY_EXTRA);
    }

    protected function process_standard($renderer, $renderable, $form) {
        $result = $renderer->render($renderable);
        if ($form) {
            $result .= $form->render();
        }
        return $result;
    }


    protected function process_diffmodels($renderer, $renderable, $form) {
        $data = $this->get_step_data();
        $dm = new data_model_matcher($data->originimportid,
                $data->destimportid);
        $data->matchedentities = $dm->get_matched_entities_list();
        $data->unmatchedentities = $dm->get_unmatched_entities_list();
        $data->orphanedentities = $dm->get_orphaned_entities_list();
        $this->raw_set_step_data($data);
        $result = $renderer->render($renderable);
        if ($form) {
            $result .= $form->render();
        }
        return $result;
    }

    protected function process_diffmodelsmodifications($renderer, $renderable, $form) {
        global $OUTPUT;
        $result = $renderer->render($renderable);
        if (empty($this->get_step_data())) {
            /* @var \core_renderer $OUTPUT */
            $message = $result . $OUTPUT->notification(get_string('dmc:expired', 'local_cveteval'), notification::ERROR);
            return $OUTPUT->single_button(get_string('continue'), new \moodle_url('/local/cveteval/admin/datamigration/index.php'));
        }
        if ($form) {
            $result .= $form->render();
        }
        return $result;
    }

    protected function process_userdatamigration($renderer, $renderable, $form) {
        // For each appraisal, appraisal criteria and final eval attached to the old model,create a copy.
        $result = $renderer->render($renderable);
        return $result;
    }


    public function get_widget() {
        $widgetclass = '\local_cveteval\output\\dmc_' . self::STEPS[$this->currentstep] . '_widget';
        return new $widgetclass($this);
    }

    public function get_form($renderable = null) {
        static $form = null;
        if (empty($form)) {
            $formclass = '\local_cveteval\local\forms\\dmc_' . self::STEPS[$this->currentstep] . '_form';
            $form = class_exists($formclass) ? new $formclass(null, ['dmc' => $this, 'renderable' => $renderable]) : null;
        }
        return $form;
    }

    public function get_next_step() {
        $step = self::STEPS[$this->currentstep + 1] ?? '';
        $step = $this->is_next_step_allowed() ? $step : '';
        return $step;
    }

    public function get_previous_step() {
        $step = self::STEPS[$this->currentstep - 1] ?? '';
        $step = $this->is_previous_step_allowed() ? $step : '';
        return $step;
    }

    private function is_previous_step_allowed() {
        $step = self::STEPS[$this->currentstep] ?? '';
        if (in_array($step, ['userdatamigration', 'final'])) {
            return false;
        }
        return true;
    }

    private function is_next_step_allowed() {
        $data = $this->get_step_data();
        switch ($this->currentstep) {
            case self::CHOOSE_HISTORY_STEP:
                return !empty($data->originimportid)
                        && !empty($data->destimportid);
        }
        return true;
    }

}