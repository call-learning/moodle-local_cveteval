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
namespace local_cveteval\local\persistent\situation;
use local_cltools\local\crud\form\entity_form;

/**
 * Situation form
 *
 * @package   local_cveteval
 * @copyright 2022 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form extends entity_form {

    /** @var string The fully qualified classname. */
    protected static $persistentclass = entity::class;

    /** @var array Fields to remove when getting the final data. */
    protected static $fieldstoremove = array('submitbutton', 'files');

    /** @var string[] $foreignfields */
    protected static $foreignfields = array();
}
