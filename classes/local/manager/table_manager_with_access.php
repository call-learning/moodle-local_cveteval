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

namespace local_cveteval\local\manager;

use local_cltools\local\crud\entity_table;
use moodle_url;
use pix_icon;
use popup_action;

/**
 * Manage table and access to tools
 *
 * For each entity it will check for access and remove the relevant action button
 *
 * @package   local_cveteval
 * @copyright 2022 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class table_manager_with_access extends entity_table {
    /**
     * Format the action cell.
     *
     * @param object $row
     * @return string
     */
    protected function col_actions(object $row): string {
        // That will render a template from a json instead.
        global $OUTPUT;
        $actions = [];
        foreach ($this->actionsdefs as $a) {
            if (is_array($a)) {
                $a = (object) $a;
            }
            if (!$this->can_i_do($a->name, $row)) {
                continue;
            }
            $url = new moodle_url($a->url, ['id' => $row->id]);
            $popupaction = empty($a->popup) ? null :
                    new popup_action('click', $url);
            $actions[] = $OUTPUT->action_icon(
                    $url,
                    new pix_icon($a->icon,
                            get_string($a->name, $a->component ?? 'local_cltools')),
                    $popupaction
            );
        }

        return implode('&nbsp;', $actions);
    }

    /**
     * Check if we can do the following action
     *
     * @param string $action
     * @param object $row
     * @return bool
     */
    abstract protected function can_i_do(string $action, object $row): bool;
}
