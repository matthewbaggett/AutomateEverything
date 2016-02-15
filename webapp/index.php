<?php
require('vendor/autoload.php');
require('config/env.php');
require('config/mysql.php');
require('config/redis.php');
use \DevExercize\Models\Quote;

// Create Slim app
$app = new \Slim\App(
    [
        'settings' => [
            'debug'         => true,
        ]
    ]
);

// Add whoops to slim because its helps debuggin' and is pretty.
$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

// Fetch DI Container
$container = $app->getContainer();

// Instantiate and add Slim specific extension
$view = new \Slim\Views\Twig(
    __DIR__ . '/views',
    [
    'cache' => $container->get('settings')['debug'] ? false : __DIR__ . '/cache'
    ]
);

$view->addExtension(new Slim\Views\TwigExtension(
    $container->get('router'),
    $container->get('request')->getUri()
));

// Register Twig View helper
$container->register($view);

$app->get('/', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    header("Location: /redis");
    exit;
});

$app->get('/redis', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
global $redis;
    $keys = $redis->keys('*');
    $redisKeys = [];
    foreach($keys as $key){
        $redisKeys[] = [
          'key' => $key,
          'value' => $redis->get($key)
        ];
    }

    return $this->view->render($response, 'redis/view.html.twig', [
        'redisKeys' => $redisKeys
    ]);
})->setName('redis');

$app->post('/companies/lookup', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    // we're just going to redirect the user so we had a nice POST submit function and a nice
    // friendly URL for the resulting page (and for bots)
    $tickerCode = $request->getParsedBodyParam('company');
    header("Location: /companies/{$tickerCode}");
    exit;
});


// Run app
$app->run();
