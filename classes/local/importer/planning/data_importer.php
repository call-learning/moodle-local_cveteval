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

use DateTime;
use local_cveteval\local\persistent\group\entity as group_entity;
use local_cveteval\local\persistent\planning\entity as planning_entity;
use local_cveteval\local\persistent\situation\entity as situation_entity;
use moodle_exception;
use stdClass;
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

    /**
     * @var int
     */
    public $planningeventscount = 0;
    /**
     * @var int
     */
    public $planningcount = 0;
    /**
     * @var array
     */
    protected $grouping = [];
    /**
     * @var array
     */
    protected $existingdates = [];
    /**
     * @var array
     */
    protected $groups = [];
    /**
     * @var array
     */
    protected $situations = [];

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
     * Check if row is valid before transformation.
     *
     * @param array $row
     * @param int $rowindex
     * @param mixed|null $options import options
     * @throws importer_exception
     */
    public function validate_before_transform($row, $rowindex, $options = null) {
        $this->validate_from_field_definition($this->get_fields_definition(), $row, $rowindex, $options);
        foreach (['starttime' => 'Date début', 'endtime' => 'Date fin'] as $datetype => $columname) {
            $dateraw = $row[$columname] ?? '';
            $date = DateTime::createFromFormat(get_string('import:dateformat', 'local_cveteval'), trim($dateraw));
            if (empty($date)) {
                throw new importer_exception('planning:invalid' . $datetype,
                        $rowindex,
                        $columname,
                        'local_cveteval',
                        $dateraw,
                        log_levels::LEVEL_ERROR);
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
     * @throws importer_exception
     */
    public function validate_after_transform($row, $rowindex, $options = null) {
        $checkotherentities = empty($options['fastcheck']) || !$options['fastcheck'];
        // Check situations.
        foreach ($this->grouping as $groupname) {
            $situationsn = $row[$groupname] ?? '';
            $situationsn = strtoupper(trim($situationsn));
            $group = group_entity::get_record(['name' => $groupname]);
            if ($checkotherentities && empty($group)) {
                throw new importer_exception('planning:groupdoesnotexist',
                        importer_exception::ROW_HEADER_INDEX,
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
                        log_levels::LEVEL_WARNING);
            }
        }
        foreach ($this->existingdates as $prevrowindex => $existingdate) {
            $existingstartdate = $existingdate[0];
            $existingenddate = $existingdate[1];
            $issameintervalstart =
                $row['starttime'] > $existingstartdate && $row['starttime'] < $existingenddate
                || $existingstartdate > $row['starttime'] && $existingenddate < $row['starttime'];
            $issameintervalend = $row['endtime'] > $existingstartdate && $row['endtime'] < $existingenddate;
            if ($issameintervalstart) {
                $this->throw_same_date_interval_exception($rowindex, 'Date début', $prevrowindex, $existingstartdate,
                    $existingenddate, $row['starttime'], $row['endtime']);
            }
            if ($issameintervalend) {
                $this->throw_same_date_interval_exception($rowindex, 'Date fin', $prevrowindex, $existingstartdate,
                    $existingenddate, $row['starttime'], $row['endtime']);
            }
        }
        $this->existingdates[$rowindex] = [$row['starttime'], $row['endtime']];
    }

    /**
     * Throw same interval exception if needed
     *
     * @param int $rowindex
     * @param string $fieldname
     * @param int $prevrowindex
     * @param int $existingdatestart
     * @param int $existingdateend
     * @param int $currentdatestart
     * @param int $currendateend
     * @return mixed
     * @throws importer_exception
     */
    private function throw_same_date_interval_exception($rowindex, $fieldname, $prevrowindex, $existingdatestart, $existingdateend,
            $currentdatestart, $currendateend) {
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
                log_levels::LEVEL_WARNING
        );
    }

    /**
     * Update or create planning entry.
     *
     * Prior to this we might also create a group so then students can be associated with
     * the group.
     *
     * @param array $row associative array storing the record
     * @param int $rowindex import options
     * @param mixed|null $options import options
     * @return array
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
                $snshortname = $row[$groupname] ?? '';
                $snshortname = strtoupper($snshortname);
                if ($snshortname && !empty($this->situations[$snshortname])) {
                    $record->groupid = $group->get('id');
                    $record->clsituationid = $this->situations[$snshortname]->get('id');
                    $planning = new planning_entity(0, $record);
                    $planning->create();
                    $this->planningeventscount++;
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
        $this->planningcount++;
        return $plannings;
    }
}



