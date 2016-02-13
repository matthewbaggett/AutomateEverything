const os = require("os");
const fs = require('fs');
const php = require('phpjs');
const util = require("util");

var environment = php.ksort(process.env);

// Connect to REDIS
var redis = require("redis"),
    redisReceiver = redis.createClient({
        host: environment.REDIS_PORT_6379_TCP_ADDR,
        port: environment.REDIS_PORT_6379_TCP_PORT
    });

redisReceiver.monitor(function (err, res) {
    console.log("Entering monitoring mode.");
});

redisReceiver.on("monitor", function (time, args) {
    console.log(time + ": " + util.inspect(args));
});