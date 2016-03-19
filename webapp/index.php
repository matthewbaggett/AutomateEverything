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
        $redisKeys[$key] = [
            'key' => $key,
            'value' => $redis->get($key)
        ];
    }
    ksort($redisKeys);

    return $this->view->render($response, 'redis/view.html.twig', [
        'redisKeys' => array_values($redisKeys),
    ]);
})->setName('redis');

$app->get('/power', function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {

    $powerConsumptionRecords = \AE\Models\PowerConsumption::search()
        ->order('created', 'DESC')
        ->where("created", date("Y-m-d H:i:s", strtotime('1 month ago')), ">=")
        ->exec();

    $watts = array_map(create_function('$o', 'return $o->watts;'), $powerConsumptionRecords);

    return $this->view->render($response, 'power/view.html.twig', [
        'powerConsumptionRecords' => $powerConsumptionRecords,
        'count_datapoints' => count($watts),
        'average_watts' => number_format(array_sum($watts) / count($watts), 0)
    ]);
})->setName('redis');

$app->get("/cameras", function (\Slim\Http\Request $request, \Slim\Http\Response $response, $args) {

    $onvif = new ponvif();
    $onvif->setIPAddress("10.0.0.80:5000");
    $onvif->setUsername("admin");
    $onvif->setPassword("427411");
    $onvif->initialize();
    
    #$onvif->ptz_RelativeMove("MainStream",-0.5,0,0,0);

    !\Kint::dump(
        $onvif->getSources(),
        $onvif->getCapabilities(),
        $onvif->getMediaUri(),
        $onvif->getUsername(),
        $onvif->getPassword(),
        $onvif->getPTZUri()
    );

    exit;
    return $this->view->render($response, 'camera/view.html.twig', [
        
    ]);
});

// Run app
$app->run();
