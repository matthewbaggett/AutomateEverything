<?php

namespace AE;

use Thru\RenderCat\Components\CompressableAssetDigest;
use Thru\RenderCat\Components\CssAsset;
use Thru\RenderCat\Components\CssAssetDigest;
use Thru\RenderCat\Components\JavascriptAsset;
use Slim\Http\Request;
use Slim\Http\Response;
use Thru\RenderCat\Components\JavascriptAssetDigest;
use Twig_Environment;
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

        $this->csses = new CssAssetDigest($this->debugMode);
        $this->javascripts = new JavascriptAssetDigest($this->debugMode);
        $this->addJavascriptFile('vendor/twbs/bootstrap/docs/assets/js/vendor/jquery.min.js');
        $this->addJavascriptFile('vendor/twbs/bootstrap/dist/js/bootstrap.js');
        $this->addJavascriptFile('vendor/twbs/bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js');
        $this->addCssFile('vendor/twbs/bootstrap/dist/css/bootstrap.css');
        $this->addCssFile('public/css/fix-top-nav-bar.css');
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
            $view = new \Slim\Views\Twig('src/Views', [
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
        $compressedCssPath = "cache/{$this->getCsses()->getHash()}" . ($this->isDebugMode() ? null : '.min')  . ".css";
        $compressedJsPath = "cache/{$this->getJavascripts()->getHash()}" . ($this->isDebugMode() ? null : '.min') . ".js";
        file_put_contents("public/" . $compressedCssPath, $this->getCSSes()->render());
        file_put_contents("public/" . $compressedJsPath, $this->getJavascripts()->render());
        $parameters = [];
        $parameters['csses'] = [
            $compressedCssPath
        ];
        $parameters['javascripts'] = [
            $compressedJsPath
        ];
        return $parameters;
    }

    /**
     * @return CompressableAssetDigest
     */
    public function getJavascripts()
    {
        return $this->javascripts;
    }

    /**
     * @return CompressableAssetDigest
     */
    public function getCSSes()
    {
        return $this->csses;
    }

    /**
     * @param $path
     * @return $this
     */
    public function addJavascriptFile($path)
    {
        $this->javascripts->add(new JavascriptAsset($path));
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function addCssFile($path)
    {
        $this->csses->add(new CssAsset($path));
        return $this;
    }
}
