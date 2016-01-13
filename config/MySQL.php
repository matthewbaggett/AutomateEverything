<?php
$environment = array_merge($_ENV, $_SERVER);

// Database Settings
if (isset($_SERVER['DB_PORT'])) {
    $host = parse_url($environment['DB_PORT']);

    $databaseConfig = array(
        'db_type' => 'Mysql',
        'db_hostname' => isset($host['hostname']) ? $host['hostname'] : $host['host'],
        'db_port' => $host['port'],
        'db_username' => $environment['DB_ENV_MYSQL_USER'],
        'db_password' => $environment['DB_ENV_MYSQL_PASSWORD'],
        'db_database' => $environment['DB_ENV_MYSQL_DATABASE'],
    );

} else {
    $databaseConfig = array(
        'db_type' => 'Mysql',
        'db_hostname' => "localhost",
        'db_port' => 3306,
        'db_username' => "autoeverything",
        'db_password' => "87d65w0ws713CfX",
        'db_database' => "autoeverything",
    );
}
$database = new \Thru\ActiveRecord\DatabaseLayer($databaseConfig);
