<?php
require('vendor/autoload.php');
require('config/env.php');
require('config/mysql.php');
require('config/redis.php');

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

// Write some default variables available to every template
$view->offsetSet('realtime_url', $environment['REALTIME_URL']);
$view->offsetSet('current_watts', is_numeric($redis->get('owlintuition.watts')) ? $redis->get('owlintuition.watts') : '???');

$app->get('/', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    header("Location: /redis");
    exit;
});

$app->get('/redis', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {
    global $redis;
    $keys = $redis->keys('*');
    $redisKeys = [];
    foreach ($keys as $key) {
        $redisKeys[] = [
            'key' => $key,
            'value' => $redis->get($key)
        ];
    }

    return $this->view->render($response, 'redis/view.html.twig', [
        'redisKeys' => $redisKeys
    ]);
})->setName('redis');

$app->get('/power', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {

    $powerConsumptionRecords = \AE\Models\PowerConsumption::search()
        ->order('created','DESC')
        ->where("created", date("Y-m-d H:i:s", strtotime('1 month ago')), ">=")
        ->exec();

    return $this->view->render($response, 'power/view.html.twig', [
        'powerConsumptionRecords' => $powerConsumptionRecords
    ]);
})->setName('redis');

// Run app
$app->run();
