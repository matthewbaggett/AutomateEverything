const os = require("os");
const fs = require('fs');
const request = require('request');


var environment = php.ksort(process.env);

// connect to MySQL
var mysql = require("mysql");
var mysqlConnectionOptions = {
 host: environment.DB_PORT_3306_TCP_ADDR,
 port: environment.DB_PORT_3306_TCP_PORT,
 user: environment.DB_ENV_MYSQL_USER,
 password: environment.DB_ENV_MYSQL_PASSWORD,
 database: environment.DB_ENV_MYSQL_DATABASE
};

/*
console.log(environment);
console.log(mysqlConnectionOptions);
process.exit();
*/

var mysqlConnection = mysql.createConnection(mysqlConnectionOptions);
mysqlConnection.connect();

var OWL = require('owl');
var owl = new OWL();
owl.monitor();

// Connect to REDIS
var redis = require("redis"),
    redisSender = redis.createClient({
        host: environment.REDIS_PORT_6379_TCP_ADDR,
        port: environment.REDIS_PORT_6379_TCP_PORT
    }),
    redisReceiver = redis.createClient({
        host: environment.REDIS_PORT_6379_TCP_ADDR,
        port: environment.REDIS_PORT_6379_TCP_PORT
    });

owl.on('electricity', function( event ) {
    console.log(event);
});
