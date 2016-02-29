const os = require("os");
const fs = require('fs');
const request = require('request');
const zlib = require('zlib');
const php = require('phpjs');
const redis = require("redis");
const ffmpeg = require('fluent-ffmpeg');
const exec = require('exec');

var environment = php.ksort(process.env);
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
var cameras = environment.CAMERA_PATHS.split(",");
var cameraCaptureDelayMs = 1000;
var max_part_size = 2 * 1024 * 1024; // 2MB

var AWS = require('aws-sdk');
AWS.config.region = environment.AWS_REGION;

var uploadToS3 = function(bucket, file, callback){
    var params = {
        Bucket: bucket,
        Key: file,
        ACL: 'public-read'
    };
    var s3Bucket = new AWS.S3();

    var s3Stream = require('s3-upload-stream')(s3Bucket);
    console.log("UPLOAD: " + file + ": Streaming to bucket '" + bucket + "'");
    var read = fs.createReadStream(file);
    var upload = s3Stream.upload(params);

    upload.maxPartSize(max_part_size);
    upload.concurrentParts(5);

    // Handle errors.
    upload.on('error', function (error) {
        console.log("UPLOAD: " + file + ": S3 Upload Error: " + error);
        redisSender.publish("errors", error);
    });

    upload.on('part', function (details) {
        var percentage_complete = (100/details.receivedSize) * details.uploadedSize;
        console.log("UPLOAD: " + file + ": Part Number " + details.PartNumber + " is " + Math.floor(percentage_complete) + "% complete.");
    });

    upload.on('uploaded', function (details) {
        console.log("UPLOAD: " + file + ": Done. Available at " + details.Location);
        redisSender.publish("upload_complete", {
            file: file,
            location: details.Location
        });
        if(callback){
            callback(details.Location);
        }
    });

    read.pipe(upload);
};

var cameraCapture = function(){
    cameras.forEach(function(camera, index){
        console.log("Take shot from " + camera);
        var command = ffmpeg(camera);
        command.format('image2');
        command.output('screenshots/screenshot.' + index + '.png');
        //command.fps(0.3);
        command.duration(1);

        command.on('filenames', function(filenames) {
            console.log('Will generate ' + filenames.join(', '))
        });
        command.on('error', function(err) {
            console.log('An error occurred: ' + err.message);
        });
        command.on('end', function() {
            console.log('Processing finished !');
        });
        command.on('start', function(commandLine) {
            console.log('Spawned Ffmpeg with command: ' + commandLine);
        });
        command.on('progress', function(progress) {
            console.log('Processed ' + index);
            uploadToS3(environment.AWS_S3_BUCKET_RAW, 'screenshots/screenshot.' + index + '.png', function(){
                console.log("Uploaded! " + index);
            });
        });
        command.run();
    });
};

setTimeout(cameraCapture, cameraCaptureDelayMs);
//setInterval(cameraCapture, cameraCaptureDelayMs);
