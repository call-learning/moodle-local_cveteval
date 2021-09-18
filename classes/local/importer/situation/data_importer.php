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
 * Clinical Situation Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\situation;
defined('MOODLE_INTERNAL') || die();

use coding_exception;
use core_user;
use dml_exception;
use local_cltools\local\crud\entity_utils;
use local_cveteval\event\role_importation_failed;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use tool_importer\field_types;
use tool_importer\importer_exception;

/**
 * Class data_importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_importer extends \tool_importer\data_importer {
    /**
     * data_importer constructor.
     *
     * @param null $defaultvals additional default values
     * @throws dml_exception
     */
    public function __construct($defaultvals = null) {
        $this->defaultvalues = ['descriptionformat' => FORMAT_HTML, 'evalgridid' => 0];
        if ($defaultvals) {
            $this->defaultvalues = array_merge($this->defaultvalues, $defaultvals);
        }
    }

    /**
     * Get the field definition array
     *
     * The associative array has at least a series of column names
     * Types are derived from the field_types class
     * 'fieldname' => [ 'type' => TYPE_XXX, ...]
     *
     * @return array
     * @throws coding_exception
     */
    public function get_fields_definition() {
        $propertydef = situation_entity::properties_definition();
        $fielddef = [];
        foreach ($propertydef as $propname => $propdef) {
            $fielddef[$propname] = [
                'type' => ($propdef['type'] == PARAM_INT) ? field_types::TYPE_INT : field_types::TYPE_TEXT,
                'required' => entity_utils::is_property_required($propdef)
            ];
        }
        $fielddef['assessors'] = [
            'type' => field_types::TYPE_TEXT,
            'required' => true
        ];
        $fielddef['appraisers'] = [
            'type' => field_types::TYPE_TEXT,
            'required' => true
        ];
        $fielddef['evalgridid'] = [
            'type' => field_types::TYPE_INT,
            'required' => false
        ];
        return $fielddef;
    }

    /**
     * Update or create clinical situation entry
     *
     * @param array $row associative array storing the record
     * @return mixed|void
     * @throws importer_exception
     */
    protected function raw_import($row, $rowindex) {
        $this->basic_validations($row);

        $row = array_merge($this->defaultvalues, $row);

        $existingsituation = !empty($row['idnumber']) && (
            situation_entity::count_records(array('idnumber' => $row['idnumber'])));
        $sitation = null;
        $record = (object) $row;
        unset($record->assessors);
        unset($record->appraisers);
        if ($existingsituation) {
            $situation = situation_entity::get_record(array('idnumber' => $row['idnumber']));
            $situation->from_record($record);
        } else {
            $situation = new situation_entity(0, $record);
        }
        $situation->save();
        // Now sync the users.
        $assessorsemails = explode(',', $row['assessors']);
        $appraisersemails = explode(',', $row['appraisers']);

        $this->add_roles($appraisersemails, $situation->get('id'), role_entity::ROLE_APPRAISER_ID);
        $this->add_roles($assessorsemails, $situation->get('id'), role_entity::ROLE_ASSESSOR_ID);
        return $situation;
    }

    public function add_roles($emails, $clinicalsituationid, $roletype) {
        foreach ($emails as $email) {
            $email = clean_param(trim($email), PARAM_EMAIL);
            $user = core_user::get_user_by_email($email);
            if (!$user) {
                $eventparams = array(
                    'other' => ['reason' => 1, 'email' => $email]
                );

                $event = role_importation_failed::create($eventparams);
                $event->trigger();
            } else {
                $existingrecord = role_entity::get_record(
                    array('userid' => $user->id, 'clsituationid' => $clinicalsituationid, 'type' => $roletype)
                );
                if (!$existingrecord) {
                    $existingrecord = new role_entity(0,
                        (object) [
                            'userid' => $user->id,
                            'clsituationid' => $clinicalsituationid,
                            'type' => $roletype
                        ]
                    );
                }
                $existingrecord->save();
            }
        }
    }
}



