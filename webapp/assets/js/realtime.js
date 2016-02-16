var socket = io(realtime_url);

var mangle = function (string) {
    return string.replace(/:/g, '').replace(/\./g, '');
};
socket.on('redis-monitor', function (data) {
    var redisDataTable = jQuery('table#redis-data');
    var redisEventTable = jQuery('table#redis-events');
    data = JSON.parse(data);
    if (data[0] == 'set') {
        jQuery('tr[rediskey=' + mangle(data[1]) + '] td.value', redisDataTable)
            .empty()
            .append(data[2]);
    }
    if (data[0] == 'publish') {
        jQuery('tbody', redisEventTable)
            .prepend("<tr>" +
                "<td>" + data[1] + "</td>" +
                "<td>" + data[2] + "</td>" +
                "</tr>");
        var checkboxSelector = '#redis-events-shown div[redisevent='+mangle(data[1])+']';
//        console.log(checkboxSelector);
//        console.log(jQuery(checkboxSelector));
        if (jQuery(checkboxSelector).length == 0) {
            jQuery('#redis-events-shown')
                .append('<div class="checkbox" redisevent="' + mangle(data[1]) + '">' +
                    '<label><input type="checkbox" checked>' + data[1] + '</label>' +
                    '</div>');
        }
    }
});

socket.on('electricity', function (data) {
    data = JSON.parse(data);
    jQuery('.watts-counter a').empty().append(data.watts + " watts");
});

jQuery(document).on('change', '#redis-events-shown :checkbox', function () {
    
});