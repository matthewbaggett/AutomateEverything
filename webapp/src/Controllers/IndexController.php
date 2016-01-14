<?php

namespace AE\Controllers;

use AE\AutomateEverything;
use Slim\Http\Request;
use Slim\Http\Response;

/** @var $app \Slim\App */
/** @var $twig \Slim\App */
/** @var  \AE\AutomateEverything $this */

$app->get('/', function (Request $request, Response $response) {

    $parameters = array_merge(
        AutomateEverything::getInstance()->defaultViewParameters(),
        []
    );
    return $this->view->render(
        $response,
        'index.html.twig',
        $parameters
    );
});
