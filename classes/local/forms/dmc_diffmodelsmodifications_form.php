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
 * Diffmodel form
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\forms;

use cache;
use cache_store;
use html_writer;
use local_cveteval\local\datamigration\data_migration_controller;
use local_cveteval\local\datamigration\data_migration_utils;
use local_cveteval\local\datamigration\data_model_matcher;
use local_cveteval\local\persistent\history\entity as history_entity;
use local_cveteval\output\dmc_entity_renderer_base;
use local_cveteval\output\helpers\output_helper;
use moodle_exception;
use moodle_url;
use moodleform;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Choose history form
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dmc_diffmodelsmodifications_form extends moodleform implements dmc_form_interface {

    /**
     * Model modification cache
     */
    const MODEL_MODIFICATIONS_CACHE = 'dmcmodelsmodificationcache';
    /**
     * @var array
     */
    protected $entities = [];

    /**
     * Construct
     *
     * @param mixed $action the action attribute for the form. If empty defaults to auto detect the
     *              current url. If a moodle_url object then outputs params as hidden variables.
     * @param mixed $customdata if your form defintion method needs access to data such as $course
     *              $cm, etc. to construct the form definition then pass it in this array. You can
     *              use globals for somethings.
     * @param string $method if you set this to anything other than 'post' then _GET and _POST will
     *               be merged and used as incoming data to the form.
     * @param string $target target frame for form submission. You will rarely use this. Don't use
     *               it if you don't need to as the target attribute is deprecated in xhtml strict.
     * @param mixed $attributes you can pass a string of html attributes here or an array.
     *               Special attribute 'data-random-ids' will randomise generated elements ids. This
     *               is necessary when there are several forms on the same page.
     *               Special attribute 'data-double-submit-protection' set to 'off' will turn off
     *               double-submit protection JavaScript - this may be necessary if your form sends
     *               downloadable files in response to a submit button, and can't call
     *               \core_form\util::form_download_complete();
     * @param bool $editable
     * @param array $ajaxformdata Forms submitted via ajax, must pass their data here, instead of relying on _GET and _POST.
     */
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null,
            $editable = true, $ajaxformdata = null) {
        $classes = data_model_matcher::get_model_matchers_class();
        foreach ($classes as $entitymatcher) {
            $entityclass = $entitymatcher::get_entity();
            $baseclassname = data_migration_utils::get_base_class($entityclass);
            $entityname = get_string("$baseclassname:entity", 'local_cveteval');
            $this->entities[$baseclassname] = $entityname;
        }
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /**
     * Definition after form data gathered.
     *
     * @return void
     */
    public function definition_after_data() {
        $dmc = $this->_customdata['dmc'];
        $stepdata = $dmc->get_step_data();
        $data = $this->get_submitted_data();
        foreach (dmc_entity_renderer_base::ALL_CONTEXTS as $context) {
            foreach ($stepdata->$context as $entityclass => $matchs) {
                foreach ($matchs as $originid => $targetentityid) {
                    $fieldname = $this->get_field_name($context, $entityclass, $originid);
                    if (empty($data->{$context}[$entityclass][$originid])) {
                        $mform = $this->_form;
                        $mform->setDefault($fieldname, $stepdata->{$context}[$entityclass][$originid]);
                    }
                }
            }
        }

    }

    /**
     * Get field name for form
     *
     * @param array $context
     * @param object $entityclass
     * @param int $id
     * @return string
     */
    protected function get_field_name($context, $entityclass, $id) {
        // There is a bug in the way moodle transfer this info in HTML_Element.
        // The \\ is replaced by \\\\ and it does not match anymore. See  HTML_QuickForm_utils::recursiveValue.
        return $context . "[" . str_replace('\\', '__', $entityclass) . "][" . $id . "]";
    }

    /**
     * Execute action
     *
     * @param object $data
     * @return void
     * @throws moodle_exception
     */
    public function execute_action($data) {
        global $PAGE;
        $dmc = $this->_customdata['dmc'] ?? null;
        $nextmodel = null;
        /* @var data_migration_controller|null $dmc the DMC (controller) . */
        if ($dmc) {
            $stepdata = $this->convert_form_data_into_stepdata($data);
            $dmc->set_step_data($stepdata);
            $nextmodel = $this->get_next_model();
        }
        if ($nextmodel) {
            redirect(new moodle_url($PAGE->url, ['step' => $dmc->get_step(), 'model' => $nextmodel]));
        } else {
            redirect(new moodle_url($PAGE->url, ['step' => $dmc->get_next_step()]));
        }
    }

    /**
     * Convert data
     *
     * @param object $formdata
     * @return object
     */
    protected function convert_form_data_into_stepdata($formdata) {
        $stepdata = (object) [
                'originimportid' => $formdata->originimportid,
                'destimportid' => $formdata->destimportid,
        ];
        foreach (dmc_entity_renderer_base::ALL_CONTEXTS as $context) {
            $stepdata->$context = [];
            if (!empty($formdata->$context)) {
                foreach ($formdata->$context as $key => $value) {
                    $newkey = str_replace('__', '\\', $key);
                    $stepdata->{$context}[$newkey] = $value;
                }
            }
        }
        return $stepdata;
    }

    /**
     * Get next model
     *
     * @return int|string|null
     */
    protected function get_next_model() {
        $currentmodel = $this->optional_param('model', null, PARAM_RAW);
        if ($currentmodel) {
            $entitiesname = array_keys($this->entities);
            $nextindex = array_search($currentmodel, $entitiesname);
            return ($nextindex !== false && $nextindex < (count($entitiesname) - 1)) ? $entitiesname[$nextindex + 1] : null;
        } else {
            return array_key_first($this->entities);
        }
    }

    /**
     * Execute cancel
     *
     * @return void
     */
    public function execute_cancel() {
        global $PAGE;
        $dmc = $this->_customdata['dmc'] ?? null;
        $prevmodel = $this->get_prev_model();
        if ($prevmodel) {
            redirect(new moodle_url($PAGE->url, ['step' => $dmc->get_step(), 'model' => $prevmodel]));
        } else {
            redirect(new moodle_url($PAGE->url, ['step' => $dmc->get_previous_step()]));
        }
    }

    /**
     * Get previous model
     *
     * @return int|string|null
     */
    protected function get_prev_model() {
        $currentmodel = $this->optional_param('model', null, PARAM_RAW);
        if ($currentmodel) {
            $entitiesname = array_keys($this->entities);
            $previndex = array_search($currentmodel, $entitiesname);
            return ($previndex !== false && $previndex > 0) ? $entitiesname[$previndex - 1] : null;
        } else {
            return array_key_first($this->entities);
        }
    }

    /**
     * Definition
     */
    protected function definition() {
        $model = $this->get_current_model();
        $dmc = $this->_customdata['dmc'];
        $mform = $this->_form;
        $title = html_writer::start_span('h3') . $this->entities[$model] . html_writer::end_span();
        $mform->addElement('html', $title);
        if ($dmc) {
            $stepdata = $dmc->get_step_data();
            foreach (dmc_entity_renderer_base::ACTIONABLE_CONTEXTS as $context) {
                $currententityclass = null;
                $currentmatchs = [];
                foreach ($stepdata->$context as $entityclass => $matchs) {
                    $baseclassname = data_migration_utils::get_base_class($entityclass);
                    if ($baseclassname == $model && !empty($matchs)) {
                        $currententityclass = str_replace('\\\\', '\\', $entityclass);
                        $currentmatchs = $matchs;
                        break;
                    }
                }
                if ($currententityclass && $currentmatchs) {
                    $title = html_writer::start_span('h3') . get_string("dmc:$context", "local_cveteval") .
                            html_writer::end_span();
                    $headerelement = 'context' . $context;
                    $mform->addElement('header', $headerelement, $title);
                    history_entity::disable_history();
                    $filters = [];
                    $alldestentitiesoptions = $this->get_all_dest_entities($dmc, $entityclass, $model, $filters);
                    foreach ($currentmatchs as $originid => $targetentityid) {
                        $entitylabel = output_helper::output_entity_info($originid, $model);
                        $fieldname = $this->get_field_name($context, $entityclass, $originid);
                        $mform->addElement('select', $fieldname, $entitylabel, $alldestentitiesoptions,
                                $targetentityid);
                        $mform->setType($fieldname, PARAM_INT);
                        $mform->setDefault($fieldname, $targetentityid);
                    }
                    $mform->setExpanded($headerelement, true);
                }
            }
        }
        /* @var data_migration_controller|null $dmc The data migration controller . */
        $mform->addElement('hidden', 'step', $dmc->get_step());
        $mform->setType('step', PARAM_TEXT);
        $mform->addElement('hidden', 'originimportid', $stepdata->originimportid);
        $mform->setType('originimportid', PARAM_INT);
        $mform->addElement('hidden', 'destimportid', $stepdata->destimportid);
        $mform->setType('destimportid', PARAM_INT);
        $mform->addElement('hidden', 'model', $this->get_current_model());
        $mform->setType('model', PARAM_TEXT);
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('previous'));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('next'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Get current model
     *
     * @return int|mixed|string|null
     */
    protected function get_current_model() {
        $currentmodel = $this->optional_param('model', null, PARAM_RAW);
        return empty($currentmodel) ? array_key_first($this->entities) : $currentmodel;
    }

    /**
     * Helper to cache information that otherwise will be retrieved in a loop.
     *
     * @param object $dmc
     * @param string $entityclass
     * @param string $model
     * @param array $filters
     * @return array|null
     * @throws moodle_exception
     */
    protected function get_all_dest_entities($dmc, $entityclass, $model, $filters = []) {
        $cache = cache::make_from_params(cache_store::MODE_REQUEST, 'local_cveteval', self::MODEL_MODIFICATIONS_CACHE);
        $lastmodel = $cache->get('lastmodel');
        $lastalldestentitiesoptions = $cache->get('lastalldestentitiesoptions');
        if ($model == $lastmodel) {
            return $lastalldestentitiesoptions;
        }
        $stepdata = $dmc->get_step_data();
        history_entity::set_current_id($stepdata->destimportid);
        $alldestentities = $entityclass::get_records($filters);
        $alldestentitiesoptions = [];
        foreach ($alldestentities as $e) {
            $id = $e->get('id');
            $alldestentitiesoptions[$id] = output_helper::output_entity_info($id, $model);
        }
        $cache->set('lastmodel', $model);
        $cache->set('lastalldestentitiesoptions', $alldestentitiesoptions);
        return $alldestentitiesoptions;
    }
}
