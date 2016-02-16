const php = require('phpjs');const redis = require("redis");const mysql = require("mysql");const moment = require('moment');var environment = php.ksort(process.env);// Connect to REDISvar redisReceiver = redis.createClient({    host: environment.REDIS_PORT_6379_TCP_ADDR,    port: environment.REDIS_PORT_6379_TCP_PORT});console.log(environment);var mysqlConnectionOptions = {    host: 'mysql',    port: 3306,    user: process.env.MYSQL_ENV_MYSQL_USER,    password: process.env.MYSQL_ENV_MYSQL_PASS,    database: process.env.MYSQL_ENV_ON_CREATE_DB};console.log(mysqlConnectionOptions);var mysqlConnection = mysql.createConnection(mysqlConnectionOptions);redisReceiver.on("message", function(channel, message){    console.log("Message on channel " + channel + ": " + message);    message = JSON.parse(message);    if(channel == "electricity"){        mysqlConnection.connect();        var timestamp = moment().format('YYYY-MM-DD HH:mm:ss');        mysqlConnection.query("INSERT INTO `power` (`watts`, `created`) VALUES (" + message.watts + " , " + timestamp + ")");        console.log("Wrote power consumption (" + message.watts + ") to database.");    }});redisReceiver.subscribe("electricity");