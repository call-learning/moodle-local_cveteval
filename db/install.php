<?php
// This file is part of Moodle - https://moodle.org/
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
 * Plugin upgrade steps are defined here.
 *
 * @package     local_cveteval
 * @category    upgrade
 * @copyright   2020 CALL Learning <contact@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_cveteval\utils;

/**
 * Execute local_cveteval upgrade from the given old version.
 *
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 */
function xmldb_local_cveteval_install() {
    utils::create_scale_if_not_present();
    utils::setup_mobile_service(true);
    utils::create_update_default_criteria_grid();
    return true;
}
