<?php

require_once("vendor/autoload.php");

$environment = array_merge($_SERVER, $_ENV);
ksort($environment);

$cameras = [];

foreach($environment as $key => $value){
    if(substr($key, 0, 7) == 'CAMERA_'){
        $elements = explode("_", $key);
        $cameras[$elements[1]][$elements[2]] = $value;
        $cameras[$elements[1]]['NAME'] = $elements[1];
    }
}
unset($cameras['']);
echo "\nStarting monitors...\n";
sleep(5); // Give ffserver a chance to boot
foreach($cameras as $camera){
    $pid = pcntl_fork();
    if ($pid == -1) {
        die ("Could not fork :(\n");
    } else if ($pid) {
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
        $videoProcess->run();
        exit;

    }
}
while(true){
    sleep(30);
}