<?php

namespace AE;

use Slim\Http\Request;
use Slim\Http\Response;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Slim\App as SlimApp;

class AutomateEverything
{
    /** @var Twig_Environment */
    private $twig;
    /** @var \Slim\App */
    private $slim;

    private $debugMode = false;

    static private $instance;

    private $csses;
    private $javascripts;

    /**
     * @return AutomateEverything
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->__init_env();
        $this->__init_slim();
        $this->__init_slim_twig_view();
        $this->__init_routes();

        $this->addJavascriptFile('vendor/twbs/bootstrap/dist/js/bootstrap.js');
        $this->addCssFile('vendor/twbs/bootstrap/dist/css/bootstrap.css');
    }

    public function run()
    {
        return $this->slim->run();
    }

    protected function __init_env()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    protected function __init_slim()
    {
        $this->slim = new SlimApp;
    }

    protected function __init_routes()
    {
        $controllers = [];
        $controllers = array_merge($controllers, $this->__scan_routes('src/Controllers'));
        foreach ($controllers as $controller) {
            $app = $this->slim;
            require_once($controller);
        }

        $this->slim->get('/hello/{name}', function (Request $request, Response $response) {
            $name = $request->getAttribute('name');
            $response->getBody()->write("Hello, $name");

            return $response;
        });
    }

    protected function __scan_routes($directory)
    {
        $controllers = [];
        $files = scandir($directory);
        foreach ($files as $file) {
            switch ($file) {
                case '.':
                case '..':
                    break;
                default:
                    $controllers[] = $directory . "/" . $file;
            }
        }
        return $controllers;
    }

    protected function __init_slim_twig_view()
    {

        // Get container
        $container = $this->slim->getContainer();

        // Register component on container
        $container['view'] = function ($container) {
            $view = new \Slim\Views\Twig('views', [
                'cache' => $this->isDebugMode()?false:'cache/twig'
            ]);
            $view->addExtension(new \Slim\Views\TwigExtension(
                $container['router'],
                $container['request']->getUri()
            ));

            return $view;
        };
    }

    public function setDebugMode($enableDebugMode = true)
    {
        $this->debugMode = $enableDebugMode;
        return $this;
    }

    public function isDebugMode()
    {
        return $this->debugMode;
    }

    public function defaultViewParameters()
    {
        $parameters = [];
        $parameters['javascripts'] = $this->getJavascripts();
        $parameters['csses'] = $this->getCSSes();
        return $parameters;
    }

    public function getJavascripts()
    {
        return $this->javascripts;
    }

    public function getCSSes()
    {
        return $this->csses;
    }

    public function addJavascriptFile($path)
    {
        $this->javascripts[] = $path;
        return $this;
    }

    public function addCssFile($path)
    {
        $this->csses[] = $path;
        return $this;
    }
}
