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

use coding_exception;
use core_user;
use dml_exception;
use local_cveteval\local\persistent\evaluation_grid\entity as evaluation_grid_entity;
use local_cveteval\local\persistent\role\entity as role_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use local_cveteval\utils;
use moodle_exception;
use tool_importer\local\exceptions\importer_exception;
use tool_importer\local\exceptions\validation_exception;
use tool_importer\local\log_levels;

/**
 * Class data_importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_importer extends \tool_importer\data_importer {
    public $rolescount = 0;
    public $situationscount = 0;
    private $shortnameuniqueids = [];
    private $defaultgridid = 0;

    /**
     * data_importer constructor.
     *
     * @param array $defaultvals additional default values
     */
    public function __construct($defaultvals = []) {
        parent::__construct(
                array_merge(
                        ['descriptionformat' => FORMAT_HTML, 'evalgridid' => 0],
                        $defaultvals
                )
        );
        $defaultgrid = evaluation_grid_entity::get_default_grid();
        $this->defaultgridid = $defaultgrid->get('id');
    }

    /**
     * Called just before importation or validation.
     *
     * Gives a chance to reinit values or local information before a real import.
     *
     * @param mixed|null $options additional importer options
     */
    public function init($options = null) {
        $this->shortnameuniqueids = [];
        $this->rolescount = 0;
        $this->situationscount = 0;
    }

    /**
     * Check if row is valid before transformation.
     *
     * @param array $row
     * @param int $rowindex
     * @param mixed|null $options import options
     * @throws validation_exception
     */
    public function validate_before_transform($row, $rowindex, $options = null) {
        $checkotherentities = empty($options['fastcheck']) || !$options['fastcheck'];
        parent::validate_before_transform($row, $rowindex);
        if (!empty($row['GrilleEval'])) {
            $trimmedval = trim($row['GrilleEval']);
            $grid = evaluation_grid_entity::get_record(array('idnumber' => $trimmedval));
            if ($checkotherentities && !$grid) {
                throw new importer_exception('situation:gridnotfound',
                        $rowindex,
                        'GrilleEval',
                        'local_cveteval',
                        $trimmedval,
                        log_levels::LEVEL_WARNING);
            }
        }
    }

    /**
     * Check if row is valid after transformation.
     *
     *
     * @param array $row
     * @param int $rowindex
     * @param mixed|null $options import options
     * @throws validation_exception
     */
    public function validate_after_transform($row, $rowindex, $options = null) {
        $assessorsemails = explode(',', $row['assessors']);
        $appraisersemails = explode(',', $row['appraisers']);

        foreach ($assessorsemails as $email) {
            if (empty(trim($email))) {
                continue;
            }
            utils::check_user_exists_or_multiple($email, $rowindex, 'situation:multipleuserfound', 'situation:usernotfound',
                    'Evaluateur');
        }
        foreach ($appraisersemails as $email) {
            if (empty(trim($email))) {
                continue;
            }
            utils::check_user_exists_or_multiple($email, $rowindex, 'situation:multipleuserfound', 'situation:usernotfound',
                    'Observateurs');
        }
        if (in_array($row['idnumber'], $this->shortnameuniqueids)) {
            throw new importer_exception('situation:duplicateshortname',
                    $rowindex,
                    'Nom court',
                    'local_cveteval',
                    $row['idnumber'],
                    log_levels::LEVEL_ERROR);
        }
        $this->shortnameuniqueids[] = $row['idnumber'];

    }

    /**
     * Update or create clinical situation entry
     *
     * @param array $row associative array storing the record
     * @param mixed|null $options import options
     * @return situation_entity
     */
    protected function raw_import($row, $rowindex, $options = null) {
        $assessors = $row['assessors'];
        $appraisers = $row['appraisers'];
        $situationscolumns = array_keys(situation_entity::properties_definition());
        $row = array_intersect_key(array_merge($this->defaultvalues, $row),
                array_flip($situationscolumns));
        $record = (object) $row;
        // Get default evaluation grid.
        $record->evalgridid = empty($row['evalgridid']) ? $this->defaultgridid : $row['evalgridid'];
        $situation = new situation_entity(0, $record);
        $situation->create();
        $this->situationscount++;
        // Now sync the users.
        $assessorsemails = explode(',', $assessors);
        $appraisersemails = explode(',', $appraisers);

        $this->add_roles($appraisersemails, $situation->get('id'), role_entity::ROLE_APPRAISER_ID);
        $this->add_roles($assessorsemails, $situation->get('id'), role_entity::ROLE_ASSESSOR_ID);
        return $situation;
    }

    /**
     * Add roles for users
     *
     * @param $emails
     * @param $clinicalsituationid
     * @param $roletype
     * @throws coding_exception
     * @throws dml_exception
     */
    public function add_roles($emails, $clinicalsituationid, $roletype) {
        foreach ($emails as $email) {
            if (empty(trim($email))) {
                continue;
            }
            $email = clean_param(trim($email), PARAM_EMAIL);
            $user = core_user::get_user_by_email($email);

            $roledef = [
                    'userid' => $user->id,
                    'clsituationid' => $clinicalsituationid,
                    'type' => $roletype
            ];
            if (!role_entity::record_exists_select("userid = :userid AND clsituationid=:clsituationid AND type=:type", $roledef)) {
                $record = new role_entity(0,
                        (object) $roledef
                );
                $record->save();
                $this->rolescount++;
            }
        }
    }
}
