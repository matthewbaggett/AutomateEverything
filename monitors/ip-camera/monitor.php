<?php

require_once("vendor/autoload.php");

$environment = array_merge($_SERVER, $_ENV);
ksort($environment);

\Kint::dump($environment);

$cameras = [];

foreach($environment as $key => $value){
    if(substr($key, 0, 7) == 'CAMERA_'){
        $elements = explode("_", $key);
        $cameras[$elements[1]][$elements[2]] = $value;
    }
    $cameras[$elements[1]]['NAME'] = $elements[1];
}
unset($cameras['']);

while($camera = array_pop($cameras)) {
    $pid = pcntl_fork();
    if ($pid == -1) {
        die ("Could not fork :(\n");
    } else if ($pid) {
        // parent
    } else {
        // child
        \Kint::dump($camera);exit;
        $name = strtolower($camera['NAME']);
        $host = $camera['HOST'];
        $port = $camera['PORT'];
        $auth = $camera['AUTH'];
        $mediapath = $camera['MEDIAPATH'];
        echo " > Camera: {$name}\n";


        $ffmpeg_command = "ffmpeg -i rtsp://{$auth}@{$host}:{$port}{$mediapath} -c copy -map 0 -acodec mp2 -f segment -strftime 1 -segment_time 60 -segment_format mp4 {$name}_%Y-%m-%d_%H-%M-%S.mp4";
        //passthru($ffmpeg_command);
    }
}