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

namespace local_cveteval\local\persistent;

/**
 * Model with history utility
 *
 * @package   local_cveteval
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class model_with_history_util {
    public static function get_all_entity_class_with_history() {
        $files = scandir(__DIR__);
        $currentnamespace = __NAMESPACE__;
        $folders = array_filter($files, function($file) {
            return is_dir(__DIR__ . "/$file") && !in_array($file, ['.', '..']);
        });
        $classes = array_map(function($file) use ($currentnamespace) {
            return "$currentnamespace\\$file\\entity";
        }, $folders);
        return array_filter($classes, function($class) {
            return class_exists($class) && in_array(model_with_history::class, class_implements($class));
        });
    }
}
