const os = require("os");
const fs = require('fs');
const php = require('phpjs');
const util = require("util");
const redis = require("redis");
var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

var environment = php.ksort(process.env);

var default_port = 5000;

// Set up HTTP listener
http.listen(default_port, function () {
    console.log(os.hostname() + ' listening on *:' + default_port);
});

// Connect to REDIS
var redisConfig;
if(typeof environment.REDIS_OVERRIDE_HOST != 'undefined'){
    redisConfig = {
        host: environment.REDIS_OVERRIDE_HOST,
        port: environment.REDIS_OVERRIDE_PORT
    };
}else {
    redisConfig = {
        host: environment.REDIS_PORT_6379_TCP_ADDR,
        port: environment.REDIS_PORT_6379_TCP_PORT
    };
}
var redisReceiver = redis.createClient(redisConfig);
var redisMonitor = redis.createClient(redisConfig);

// Start app.
io.on('connection', function (socket) {
    //console.log('a user connected');

    socket.emit('server information', {
        host: os.hostname()
    });

    socket.on('disconnect', function () {
        //console.log('user disconnected');
    });
});


redisReceiver.on("message", function(channel, message){
    console.log("Emit: " + channel + ": " + message);
    message = JSON.parse(message);
    io.emit(channel, JSON.stringify(message));
});

redisReceiver.subscribe("electricity");

redisMonitor.monitor(function (err, res) {
    console.log("Entering monitoring mode.");
});

redisMonitor.on("monitor", function (time, args) {
    //console.log(time + ": " + util.inspect(args));
    io.emit("redis-monitor", JSON.stringify(args));
});



