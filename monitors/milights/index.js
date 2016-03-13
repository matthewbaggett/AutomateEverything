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

var inventory = {};

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
    this.milight.zone([1,2,3,4]).rgb('#FF0000');
    this.milight.zone([1,2,3,4]).off();
};

MilightsMonitor.prototype.initialiseListeners = function(){
    var scope = this;
    console.log("Subscribing to lights_request");
    redisReceiver.subscribe("lights_request");
    redisReceiver.subscribe("device_inventory_request");

    redisReceiver.on("message", function(channel, message) {
        //console.log("Message on channel " + channel + ".");
        message = JSON.parse(message);
        if (channel == "lights_request") {
            scope.parseLightsRequest(message);
        }
        if (channel == "device_inventory_request") {
            scope.parseDeviceInventoryRequest(message);
        }
    });
};

MilightsMonitor.prototype.parseLightsRequest = function(message){
    //console.log(message);
    if (typeof message.colour.white != 'undefined') {
        // Handle a white colour temperature
        this.processWhiteChange(message.colour);
    } else if (typeof message.colour.red != 'undefined') {
        // Handle a colour request
        this.processColourChange(message);
    } else {
        console.log("Invalid light request.");
    }
};

MilightsMonitor.prototype.processColourChange = function(message){
    var zone = message.scope;
    if(message.scope.toLowerCase() == "all"){
        zone = [1,2,3,4];
    }
    this.setColour(zone, message.colour);
};

MilightsMonitor.prototype.parseDeviceInventoryRequest = function(message){
    redisSender.publish('device_inventory_response', JSON.stringify(inventory));
};

MilightsMonitor.prototype.setColour = function(zone, colour){
    var scope = this;
    console.log(typeof zone);
    if(typeof zone == 'object'){
        zone.forEach(function(element, index, array){
            scope.updateInventory(element, colour);
        });
    }else {
        scope.updateInventory(zone, colour);
    }

    var colourRGB = colorspaces.make_color('sRGB', [colour.red, colour.green, colour.blue]);
    var colourHEX = colourRGB.as('hex');

    this.milight.zone(zone).rgb(colourHEX);
};

MilightsMonitor.prototype.updateInventory = function(zone, colour){
    inventory["zone_" + zone] = {
        name: 'milights' + '/' + 'zone_' + zone,
        type: 'lightbulb',
        state: {
            colour: {
                red: colour.red,
                green: colour.green,
                blue: colour.blue
            }
        }
    };
};

$milights = new MilightsMonitor;
$milights.begin();
