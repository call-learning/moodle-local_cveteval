import $ from 'jquery';

export const init = (tableuniqueid, clickurl, fieldname) => {
    $(document).on('tabulator-row-click', function(event, row, uniqueid) {
        if (uniqueid === tableuniqueid) {
            // eslint-disable-next-line no-console
            const data =  row.getData();
            if (typeof (data.id) !== "undefined") {
                window.location.href = `${clickurl}?${fieldname}=${data.id}`;
            }
        }
    });
};