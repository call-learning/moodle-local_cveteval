import $ from 'jquery';

export const init = (tableuniqueid, baseurl, parameters) => {
    $(document).on('tabulator-row-click', function (event, row, uniqueid) {
        if (uniqueid === tableuniqueid) {
            // eslint-disable-next-line no-console
            const data = row.getData();
            if (typeof (data.id) !== "undefined") {

                // if (Array.isArray(fieldname)) {
                //
                // }
                let dataparams = {};
                for(const [key, value] of Object.entries(parameters)) {
                    dataparams[key] = data[value];
                }
                const paramurl = $.param(dataparams);
                window.location.href = `${baseurl}?${paramurl}`;
            }
        }
    });
};