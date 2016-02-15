const os = require("os");
const fs = require('fs');
const php = require('phpjs');
const util = require("util");
var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);

var environment = php.ksort(process.env);

// Connect to REDIS
var redis = require("redis");
var redisReceiver = redis.createClient({
    host: environment.REDIS_PORT_6379_TCP_ADDR,
    port: environment.REDIS_PORT_6379_TCP_PORT
});

// Start app.
io.on('connection', function (socket) {
    console.log('a user connected');

    socket.emit('server information', {
        host: os.hostname(),
    });

    socket.on('disconnect', function () {
        console.log('user disconnected');
    });
});

redisReceiver.monitor(function (err, res) {
    console.log("Entering monitoring mode.");
});

redisReceiver.on("monitor", function (time, args) {
    console.log(time + ": " + util.inspect(args));
    io.emit("redis-monitor", JSON.stringify(args));
});