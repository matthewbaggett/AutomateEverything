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

var conf_file = process.env.HOME+'/.philips-hue.json';
var lightState = {};

var pollScanner = function(){
    hue.getLights()
        .then(function (lights) {
            for (var key in lights){
                if(!lights.hasOwnProperty(key)) continue;
                var light = lights[key];

                var uniqueId = light.uniqueid;
                uniqueId = uniqueId.replace(":","");

                var lightJson = JSON.stringify(light);
                if (
                    typeof lightState[uniqueId] === 'undefined' ||
                    lightJson != JSON.stringify(lightState[uniqueId])
                ) {

                    redisSender.publish("light_state_change", lightJson);
                    redisSender.set("philipshue.lights." + light.uniqueid, lightJson);
                }
                lightState[uniqueId] = light;
            }

        })
        .catch(function (err) {
            console.error(err.stack || err);
        });
};

var kelvinToCt = function(kelvin){
    kelvin = kelvin >= 5000 ? 5000 : kelvin;
    kelvin = kelvin <= 2000 ? 2000 : kelvin;
    var percent = (100/3000) * (kelvin - 2000);
    console.log("Percentage: " + percent + "%");
    percent = 100 - percent;
    console.log("Percentage: " + percent + "%");
    var ct = (((500-153) / 100) * percent) + 153;
    return Math.round(ct);
};

var processColourChange = function(key, colour){
    var colourRGB = colorspaces.make_color('sRGB', [colour.red, colour.green, colour.blue]);
    var colourCIEXYZ = colourRGB.as('CIEXYZ');
    var xy = [
        Math.round(colourCIEXYZ[0] * 100) / 100,
        Math.round(colourCIEXYZ[1] * 100) / 100
    ];
    var updatedLightState = {
        on: colour.brightness > 0,
        xy: xy,
        bri: Math.ceil(colour.brightness * 255) + 1
    };
    //console.log("updated light state:");
    //console.log(updatedLightState);
    hue
        .light(key)
        .setState(updatedLightState)
        .then(console.log)
        .catch(console.error);
};

var processWhiteChange = function(key, colour){
    colour.white = colour.white.toLowerCase().replace("k","");
    colour.white = parseInt(colour.white);
    colour.white = Math.round(colour.white);
    var colourTemperature = kelvinToCt(colour.white);
    console.log("Input colour temperature: " + colour.white + "K . Output CT score: " + colourTemperature);
    var updatedLightState = {
        on: colour.brightness > 0,
        ct: colourTemperature,
        bri: Math.ceil(colour.brightness * 255) + 1
    };
    console.log("updated light state:");
    console.log(updatedLightState);
    hue
        .light(key)
        .setState(updatedLightState)
        .then(console.log)
        .catch(console.error);
}

redisReceiver.on("message", function(channel, message) {
    //console.log("Message on channel " + channel + ": " + message);
    message = JSON.parse(message);
    if (channel == "lights_request") {
        console.log("Lights request received");
        console.log(message);
        hue.getLights()
            .then(function (lights) {
                for (var key in lights) {
                    if (!lights.hasOwnProperty(key)) continue;
                    if (typeof message.colour.white != 'undefined') {
                        // Handle a white colour temperature
                        processWhiteChange(key, message.colour);
                    } else if (typeof message.colour.red != 'undefined') {
                        // Handle a colour request
                        processColourChange(key, message.colour);
                    } else {
                        console.log("Invalid light request.");
                    }
                }
            });
    }
});


hue
    .login(conf_file)
    .then(function(conf){
        return hue.light(1).on();
    })
    .then(function(res){
        redisReceiver.subscribe("lights_request");
        console.log("Result?", res);
        setInterval(pollScanner, 1000);
    })
    .catch(function(err){
        console.error(err.stack || err);
    });

