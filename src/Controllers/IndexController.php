<?php

namespace AE\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
/** @var $app \Slim\App */
/** @var $twig \Slim\App */

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'wrapper.html.twig', []);
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->view->render($response, 'profile.html', [
        'name' => $args['name']
    ]);
})->setName('profile');