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
}

foreach($cameras as $camera_name => $camera){
    $pid = pcntl_fork();
    if($pid == -1){
        die ("Could not fork :(\n");
    }else if ($pid){
        // parent
        pcntl_wait($status);
    }else{
        // child
        echo " > Camera: {$camera_name}\n";
        echo "Create blocking thread to capture video\n";
    }
}