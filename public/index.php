<?php

require_once("../bootstrap.php");

$ae = \AE\AutomateEverything::getInstance();
$ae->setDebugMode(true);
$ae->run();
