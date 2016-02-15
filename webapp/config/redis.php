<?php
// Connect to Redis
if(!isset($environment['REDIS_OVERRIDE_HOST'])){
    $redis = new Predis\Client(parse_url($environment['REDIS_1_PORT']));
}else{
    $redis = new Predis\Client([
        'scheme' => 'tcp',
        'host' => $environment['REDIS_OVERRIDE_HOST'],
        'port' => $environment['REDIS_OVERRIDE_PORT'],
    ]);
}
