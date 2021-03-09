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
 * This is a table with nested tables in rows: http://tabulator.info/examples/4.9#nested-tables
 *
 * @module   local_cltools/assess-student-table.js
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import {tableInit} from "local_cltools/local/table/dynamic";

// Same as in the usual dynamic but as we are not paginating, we send the whole data.
const ajaxSubtableResponseProcessorData = function (url, params, response) {
    return response.data.map(
        (rowstring) => JSON.parse(rowstring)
    );
};

export const init = async (tabulatorelementid) => {
    const tableelement = $("#" + tabulatorelementid);
    const tableUniqueid = tableelement.data('tableUniqueid');
    const rowFormatter = (row) => {
        const holderEl = document.createElement("div");
        const tableEl = document.createElement("div");
        holderEl.appendChild(tableEl);
        const id = row.getData().id;
        row.getElement().appendChild(holderEl);
        holderEl.style.boxSizing = "border-box";
        holderEl.style.padding = "10px 10px 10px 10px";
        holderEl.style.borderTop = "1px solid #333";
        holderEl.style.borderBotom = "1px solid #333";
        holderEl.style.background = "#ddd";
        holderEl.setAttribute('class', "appraisalSubtable" + id + "");

        tableEl.style.border = "1px solid #333";
        tableEl.setAttribute('class', "appraisalSubtable" + id + "");

        const filters = {
            jointype: 2,
            filters: {
                appraisalid: {
                    name: "appraisalid", jointype: 2, values:
                        [JSON.stringify({direction: "==", value: id})],
                    type: "numeric_comparison_filter"
                }
            }
        };
        tableInit(
            tableEl,
            'local_cveteval\\local\\assessment\\appraisals_criteria',
            tableUniqueid + id.toString(),
            0,
            filters,
            () => null,
            {
                pagination: false,
                ajaxFiltering: false,
                ajaxSorting: false,
                paginationDataReceived: false,
                layout:"fitColumns",
                ajaxResponse: ajaxSubtableResponseProcessorData
            }
        );
    };
    tableInit("#" + tabulatorelementid,
        tableelement.data('tableHandler'),
        tableUniqueid,
        tableelement.data('table-pagesize'),
        tableelement.data('tableFilters'),
        (e, row) =>   {
                const id = row.getData().id;
                if (e.target.className === "tabulator-cell") {
                    $(".appraisalSubtable" + id + "").toggle();
                }
        },
        {
            rowFormatter: rowFormatter,
            selectable: true,
            resizableColumns: false,
            layout: "fitColumns",
            minHeight: "400px"
        }
    );
};
