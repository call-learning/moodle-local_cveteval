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
 * This this provides user selection ajax transport and result processing.
 *
 * @module   local_cltools/user_selector.js
 * @copyright 2023 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Ajax from "core/ajax";
import Notification from 'core/notification';
/**
 * This is the transport function for the user selector.
 * @param selector
 * @param query
 * @param callback
 * @param failure
 */
export const transport = (selector, query, callback, failure) => {
    Ajax.call([{
        methodname: 'local_cveteval_get_users',
        args: {
            'search': query
        }
    }
    ])[0].then((response) => {
        if (response) {
            callback(response.users);
        } else {
            failure();
        }
        return response;
    }).catch(Notification.exception);
};

/**
 * This is the result processing function for the user selector.
 * @param selector
 * @param results
 * @return {{results: *}}
 */
export const processResults = (selector, results) => {
    const users = results.map((user) => {
        return {
            value: user.id,
            label: `${user.fullname} (${user.email})`
        };
    });
    return users;
};