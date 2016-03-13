const php = require('phpjs');
const redis = require("redis");
const colorspaces = require('colorspaces');
var environment = php.ksort(process.env);

// Connect to REDIS
var redisSender = redis.createClient({
    host: environment.REDIS_PORT_6379_TCP_ADDR,
    port: environment.REDIS_PORT_6379_TCP_PORT
});
var redisReceiver = redis.createClient({
    host: environment.REDIS_PORT_6379_TCP_ADDR,
    port: environment.REDIS_PORT_6379_TCP_PORT
});

var lightState = {};

var Milight = require("milight");

function MilightsMonitor(){

}

MilightsMonitor.prototype.milight = new Milight();

MilightsMonitor.prototype.begin = function(){
    this.initialiseListeners();
    var connectionDetails = {
        host: environment.MILIGHTS_HOST,
        broadcast: true
    };
    this.milight = new Milight(connectionDetails);

    this.milight.on();
    this.milight.zone(1).rgb("#FF0000");
};

MilightsMonitor.prototype.initialiseListeners = function(){

    var scope = this;
    console.log("Subscribing to lights_request");
    redisReceiver.subscribe("lights_request");

    redisReceiver.on("message", function(channel, message) {
        //console.log("Message on channel " + channel + ": " + message);
        message = JSON.parse(message);
        if (channel == "lights_request") {
            console.log("Lights request received");
            scope.parseLightsRequest(message);
        }
    });
};

MilightsMonitor.prototype.parseLightsRequest = function(message){
    console.log(message);
    if (typeof message.colour.white != 'undefined') {
        // Handle a white colour temperature
        this.processWhiteChange(message.colour);
    } else if (typeof message.colour.red != 'undefined') {
        // Handle a colour request
        this.processColourChange(message.colour);
    } else {
        console.log("Invalid light request.");
    }
};

MilightsMonitor.prototype.processColourChange = function(colour){
    var colourRGB = colorspaces.make_color('sRGB', [colour.red, colour.green, colour.blue]);
    var colourHEX = colourRGB.as('hex');
    this.milight.zone([1,2,3,4]).rgb(colourHEX);
};

$milights = new MilightsMonitor;
$milights.begin();
