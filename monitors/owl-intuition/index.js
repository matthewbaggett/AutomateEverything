const php = require('phpjs');
const OWL = require("owlintuition");

var environment = php.ksort(process.env);

var owl = new OWL();
owl.monitor();

// Connect to REDIS
var redis = require("redis"),
    redisSender = redis.createClient({
        host: environment.REDIS_PORT_6379_TCP_ADDR,
        port: environment.REDIS_PORT_6379_TCP_PORT
    });

owl.on('electricity', function( event ) {
    event = JSON.parse(event);
    console.log("Drawing " + event.channels[0][0].current + event.channels[0][0].units);
    redisSender.set('owlintuition.watts', event.channels[0][0].current);
    redisSender.publish('electricity', JSON.stringify({
        watts: event.channels[0][0].current
    }));
    redisSender.publish('owl-state', JSON.stringify({
        battery: event.battery,
        signal: event.signal
    }));
});

owl.on('weather', function( event ) {
    redisSender.publish('weather', JSON.stringify({
       event: event
    }));
});

owl.on('error', function( error ) {
    redisSender.publish('errors', JSON.stringify({
        service: "monitor-owl-intuition",
        subservice: "owl network unit",
        more: event
    }));
});
console.log("Started, waiting for data");