<?php

require_once("vendor/autoload.php");
use Predis\Client as RedisClient;

$environment = array_merge($_SERVER, $_ENV);
ksort($environment);

$cameras = [];

foreach ($environment as $key => $value) {
    if (substr($key, 0, 7) == 'CAMERA_') {
        $elements = explode("_", $key);
        $cameras[$elements[1]][$elements[2]] = $value;
        $cameras[$elements[1]]['NAME'] = $elements[1];
    }
}
// Connect to Redis
if (!isset($environment['REDIS_OVERRIDE_HOST'])) {
    $redisClient = new RedisClient(parse_url($environment['REDIS_1_PORT']));
} else {
    $redisClient = new RedisClient([
        'scheme' => 'tcp',
        'host' => $environment['REDIS_OVERRIDE_HOST'],
        'port' => $environment['REDIS_OVERRIDE_PORT'],
    ]);
}

unset($cameras['']);
echo "\nStarting monitors...\n";
sleep(5); // Give ffserver a chance to boot
foreach ($cameras as $camera) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die("Could not fork :(\n");
    } elseif ($pid) {
        // parent
        continue;
    } else {
        // child
        $name = strtolower($camera['NAME']);
        $host = $camera['HOST'];
        $port = $camera['PORT'];
        $auth = $camera['AUTH'];
        $mediapath = $camera['MEDIAPATH'];
        echo " > Camera: {$name}\n";

        $videoProcess = new \AE\IpCamera\VideoProcess($name, "rtsp://{$auth}@{$host}:{$port}{$mediapath}");
        // set segment time to 1/4 hour
        $videoProcess
            ->setRedis($redisClient)
            ->setSegmentTime(60*(isset($environment['CAMERA_SEGMENT_TIME_MINUTES'])?$environment['CAMERA_SEGMENT_TIME_MINUTES']:30))
            ->run();
        exit;
    }
}
while (true) {
    sleep(30);
}
