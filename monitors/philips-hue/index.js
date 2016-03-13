const php = require('phpjs');
const Hue = require('philips-hue');
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

var hue = new Hue();

var inventory = {};

var pollScannerDelayms = 1000;

var lightState = {};


function PhilipsHueMonitor(){

}


PhilipsHueMonitor.prototype.philipsHueAuth = function() {
    var scope = this;
    redisReceiver.get("philipshue.auth.login", function(err, reply){
        var auth;
        console.log("reply from 'philipshue.auth.login': " + reply);
        if(reply == null || reply == '{}'){
            //do Auth
            auth = {};
            console.log("Searching for bridges");
            hue.getBridges()
                .then(function(bridges){
                    console.log("Bridges:", bridges);
                    var bridge = bridges[0]; // use 1st bridge
                    console.log("bridge: "+bridge);
                    auth.ip = bridge;
                    return hue.auth(bridge);
                })
                .then(function(username) {
                    auth.username = username;
                    console.log("username: " + username);
                    redisSender.set("philipshue.auth.login", JSON.stringify(auth));
                    scope.philipsHueAuth();
                })
                .catch(function(err){
                    console.error(err.stack || err);
                    scope.philipsHueAuth();
                });
        }else {
            auth = JSON.parse(reply);
        }

        if(typeof auth.ip != 'undefined') {
            hue = new Hue;
            hue.bridge = auth.ip;
            hue.username = auth.username;
            redisSender.set("philipshue.auth.login", JSON.stringify(auth));
            setInterval(function() {
                console.log("bloop");
                scope.pollScanner(scope);
            }, pollScannerDelayms
            );
            scope.startSubscribers();
        }
    });
};


PhilipsHueMonitor.prototype.begin = function(){
    var scope = this;
    this.philipsHueAuth();
    redisReceiver.subscribe("device_inventory_request");
    redisReceiver.on("message", function(channel, message) {
        message = JSON.parse(message);
        if (channel == "lights_request") {
            scope.parseLightsRequest(message);
        }
        if (channel == "device_inventory_request") {
            scope.parseDeviceInventoryRequest();
        }
    });
};

PhilipsHueMonitor.prototype.parseDeviceInventoryRequest = function(message){
    redisSender.publish('device_inventory_response', JSON.stringify({
        agent: 'monitor-philips-hue',
        inventory: inventory
    }));
};

PhilipsHueMonitor.prototype.parseLightsRequest = function(message){
    hue.getLights()
        .then(function (lights) {
            for (var key in lights) {
                if (lights.hasOwnProperty(key)) {
                    if (typeof message.colour.white != 'undefined') {
                        // Handle a white colour temperature
                        scope.processWhiteChange(key, message.colour);
                    } else if (typeof message.colour.red != 'undefined') {
                        // Handle a colour request
                        scope.processColourChange(key, message.colour);
                    } else {
                        console.log("Invalid light request.");
                    }
                }
            }
        });
}

PhilipsHueMonitor.prototype.startSubscribers = function(){
    redisReceiver.subscribe("lights_request");
};

PhilipsHueMonitor.prototype.kelvinToCt = function(kelvin){
    kelvin = kelvin >= 5000 ? 5000 : kelvin;
    kelvin = kelvin <= 2000 ? 2000 : kelvin;
    var percent = (100/3000) * (kelvin - 2000);
    percent = 100 - percent;
    var ct = (((500-153) / 100) * percent) + 153;
    return Math.round(ct);
}

PhilipsHueMonitor.prototype.processColourChange = function(key, colour){
    var colourRGB = colorspaces.make_color('sRGB', [colour.red, colour.green, colour.blue]);
    var colourCIEXYZ = colourRGB.as('CIEXYZ');
    var xy = [
        Math.round(colourCIEXYZ[0] * 100) / 100,
        Math.round(colourCIEXYZ[1] * 100) / 100
    ];
    var updatedLightState = {
        on: colour.brightness > 0,
        xy: xy,
        bri: Math.ceil(colour.brightness * 255)
    };
    hue
        .light(key)
        .setState(updatedLightState)
        .catch(console.error);
};

PhilipsHueMonitor.prototype.processWhiteChange = function(key, colour){
    colour.white = colour.white.toLowerCase().replace("k","");
    colour.white = parseInt(colour.white);
    colour.white = Math.round(colour.white);
    var colourTemperature = this.kelvinToCt(colour.white);
    var updatedLightState = {
        on: colour.brightness > 0,
        ct: colourTemperature,
        bri: Math.ceil(colour.brightness * 255) + 1
    };
    hue
        .light(key)
        .setState(updatedLightState)
        .catch(console.error);
};

PhilipsHueMonitor.prototype.updateInventory = function(light){
    var col = colorspaces.make_color('CIEXYZ', [light.state.xy[0], light.state.xy[1], light.state.bri/254]);
    var colour = col.as('sRGB');
    console.log("updateInventory");
    inventory[light.name] = {
        name: 'milights' + '/' + light.name,
        type: 'lightbulb',
        state: {
            on: light.state.on,
            brightness: light.state.bri/254,
            colour: {
                red: colour[0],
                green: colour[1],
                blue: colour[2]
            }
        }
    };
};

PhilipsHueMonitor.prototype.pollScanner = function(scope){
    console.log("pollscanner");
    hue.getLights()
        .then(function (lights) {
            for (var key in lights) {
                if (!lights.hasOwnProperty(key)) continue;
                var light = lights[key];

                var uniqueId = light.uniqueid;
                uniqueId = uniqueId.replace(":", "");

                var lightJson = JSON.stringify(light);
                if (
                    typeof lightState[uniqueId] === 'undefined' ||
                    lightJson != JSON.stringify(lightState[uniqueId])
                ) {

                    redisSender.publish("light_state_change", lightJson);
                    redisSender.set("philipshue.lights." + light.uniqueid, lightJson);
                }
                lightState[uniqueId] = light;
                scope.updateInventory(light);
            }
        })
        .catch(function (err) {
            console.error(err.stack || err);
        });
};

var lights = new PhilipsHueMonitor();
lights.begin();