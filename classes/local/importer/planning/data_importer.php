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
 * Grouping Importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_cveteval\local\importer\planning;
defined('MOODLE_INTERNAL') || die();

use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use moodle_exception;
use stdClass;
use tool_importer\local\exceptions\importer_exception;
use tool_importer\local\exceptions\validation_exception;
use tool_importer\local\import_log;
use tool_importer\local\log_levels;

/**
 * Class data_importer
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_importer extends \tool_importer\data_importer {

    protected $grouping = [];

    protected $existingdates = [];

    protected $groups = [];

    protected $situations = [];

    public $planningeventscount = 0;
    public $planningcount = 0;

    /**
     * Called just before importation or validation.
     *
     * Gives a chance to reinit values or local information before a real import.
     *
     * @param mixed|null $options additional importer options
     */
    public function init($options = null) {
        foreach (array_keys($this->get_source()->get_fields_definition()) as $name) {
            if (!preg_match('/date.*/', strtolower($name))) {
                $this->grouping[] = $name;
            }
        }
        $this->existingdates = [];
        $this->situations = [];
        $this->groups = [];
        $this->planningeventscount = 0;
        $this->planningcount = 0;
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
        $checkotherentities = empty($options['fastcheck']) ? true : !$options['fastcheck'];
        // Check situations.
        foreach ($this->grouping as $groupname) {
            $situationsn = $row[$groupname] ?? '';
            $group = group_entity::get_record(['name' => $groupname]);
            if ($checkotherentities  && empty($group)) {
                throw new importer_exception('planning:groupdoesnotexist',
                    $rowindex,
                    $groupname,
                    'local_cveteval',
                    $groupname,
                    log_levels::LEVEL_ERROR);
            }
            if ($checkotherentities && !empty(trim($situationsn)) &&
                    !situation_entity::record_exists_select("idnumber = :situationsn", ['situationsn' => $situationsn])) {
                throw new importer_exception('planning:situationnotfound',
                    $rowindex,
                    $groupname,
                    'local_cveteval',
                    $situationsn,
                    log_levels::LEVEL_ERROR);
            }
        }
        foreach ($this->existingdates as $prevrowindex => $existingdate) {
            $issameintervalstart =
                $row['starttime'] >= $existingdate[0] && $row['starttime'] <= $existingdate[1]
                ||  $existingdate[0] >= $row['starttime']  && $existingdate[1] <= $row['starttime'];
            $issameintervalend = $row['endtime'] >= $existingdate[0] && $row['endtime'] <= $existingdate[1];
            if ($issameintervalstart) {
                $this->throw_same_date_interval_exception($rowindex, 'Date début', $prevrowindex, $existingdate[0], $existingdate[1], $row['starttime'], $row['endtime']);
            }
            if ($issameintervalend) {
                $this->throw_same_date_interval_exception($rowindex, 'Date fin', $prevrowindex, $existingdate[0],
                    $existingdate[1], $row['starttime'], $row['endtime']);
            }
        }
        $this->existingdates[$rowindex] = [$row['starttime'], $row['endtime']];
    }


    private function throw_same_date_interval_exception($rowindex, $fieldname, $prevrowindex, $existingdatestart, $existingdateend, $currentdatestart, $currendateend) {
        throw new importer_exception(
            'planning:dateoverlaps',
            $rowindex,
            $fieldname,
            'local_cveteval',
            [
                'prevrowindex' => $prevrowindex + 2,
                'previousstartdate' => userdate($existingdatestart, get_string('strftimedatefullshort', 'core_langconfig')),
                'previousenddate' => userdate($existingdateend, get_string('strftimedatefullshort', 'core_langconfig')),
                'currentstartdate' => userdate($currentdatestart, get_string('strftimedatefullshort', 'core_langconfig')),
                'currentenddate' => userdate($currendateend, get_string('strftimedatefullshort', 'core_langconfig')),
            ],
            log_levels::LEVEL_ERROR
        );
    }
    /**
     * Update or create planning entry.
     *
     * Prior to this we might also create a group so then students can be associated with
     * the group.
     *
     * @param array $row associative array storing the record
     * @param mixed|null $options import options
     * @return mixed|void
     * @throws importer_exception
     */
    protected function raw_import($row, $rowindex, $options = null) {
        $row = array_merge($this->defaultvalues, $row);

        // Preload groups and clinical situations.
        if (empty($this->groups)) {
            foreach ($this->grouping as $groupname) {
                $this->groups[$groupname] = group_entity::get_record(['name' => $groupname]);
            }
        }
        if (empty($this->situations)) {
            $clsituationsrecords = situation_entity::get_records();
            foreach ($clsituationsrecords as $record) {
                $this->situations[$record->get('idnumber')] = $record;
            }
        }
        // Now the row and add a planning instance for each group and clinical situation.
        $plannings = [];
        foreach ($this->groups as $groupname => $group) {
            try {
                $record = new stdClass();
                $record->starttime = $row['starttime'];
                $record->endtime = $row['endtime'];
                if (!empty($row[$groupname]) && !empty($this->situations[$row[$groupname]])) {
                    $record->groupid = $group->get('id');
                    $record->clsituationid = $this->situations[$row[$groupname]]->get('id');
                    $planning = new planning_entity(0, $record);
                    $planning->create();
                    $this->planningeventscount ++;
                    $plannings[] = $planning;
                }
            } catch (moodle_exception $e) {
                $this->get_logger()->log_from_exception($e, [
                    'linenumber' => $rowindex,
                    'module' => $this->module,
                    'origin' => $this->get_source()->get_origin(),
                    'importid' => $this->get_import_id()
                ]);
            }
        }
        $this->planningcount ++;
        return $plannings;
    }
}



