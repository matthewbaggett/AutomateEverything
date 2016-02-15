<?php

$environment = array_merge($_SERVER, $_ENV);
ksort($environment);

// Lets connect to a database
$databaseConfigurationHost = parse_url($environment['MYSQL_PORT']);
$databaseConfiguration = array(
    'db_type'       => 'Mysql',
    'db_hostname'   => $databaseConfigurationHost['host'],
    'db_port'       => $databaseConfigurationHost['port'],
    'db_username'   => $environment['MYSQL_1_ENV_MYSQL_USER'],
    'db_password'   => $environment['MYSQL_1_ENV_MYSQL_PASSWORD'],
    'db_database'   => $environment['MYSQL_1_ENV_MYSQL_DATABASE'],
);
$database = new \Thru\ActiveRecord\DatabaseLayer($databaseConfiguration);
