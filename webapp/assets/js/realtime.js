var socket = io('http://localhost:9998');

var mangle = function(string){
  return string.replace(/:/g, '').replace(/\./g, '');
};
socket.on('redis-monitor', function (data) {
    var redisDataTable = jQuery('table#redis-data');
    var redisEventTable = jQuery('table#redis-events');
    data = JSON.parse(data);
    if(data[0] == 'set'){
        jQuery('tr[rediskey=' + mangle(data[1]) + '] td.value', redisDataTable)
            .empty()
            .append(data[2]);
    }
    if(data[0] == 'publish'){
        jQuery('tbody', redisEventTable)
            .prepend("<tr>" +
                "<td>" + data[1] + "</td>" +
                "<td>" + data[2] + "</td>" +
                "</tr>");

    }
});