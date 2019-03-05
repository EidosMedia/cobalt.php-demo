<?php

namespace App;

use App\Controller\PageController;
use Eidosmedia\Cobalt\CobaltSDK;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Stringy\StaticStringy as S;

class Autoload {
    
    private $app = null;
    private $container = null;
    private $sdk = null;
    private $settings = null;
    private $sitemap = null;
    private $siteService = null;
    
    public function __construct($settings) {
        // load slim, twig and other dependencies only if autoload class is instanciated
        require_once('../vendor/autoload.php');
        $this->settings = $settings;

        // start slim/twig with all settings from settings.php
        $this->app = new App(['settings' => $settings]);

        // set routes based on settings.php
        $this->setRoutes();

        // initialise Cobalt SDK
        $this->initCobaltSDK();

        $this->container = $this->app->getContainer();
        $this->container['cobalt'] = $this->sdk;
        $this->container['siteService'] = $this->siteService;
        $this->container['sitemap'] = $this->sitemap;

        $this->setViewHandler();
        $this->setControllerHandler();
    }

    public function setRoutes() {
        // set routes for HTTP GET
        foreach ($this->settings['routes'] as $pattern => $controllerMethod) {
            $this->app->get($pattern, $controllerMethod);
        }
    }

    public function initCobaltSDK() {
        $this->sdk = new CobaltSDK($this->settings['discoveryUri']);
        $this->siteService = $this->sdk->getSiteService($this->settings['siteName']);
        $this->sitemap = $this->siteService->getSitemap();
    }

    public function setViewHandler() {
        $this->container['view'] = function($container) {
            $view = new Twig($container->get('settings')['templatePath'], [
                'cache' => false
            ]);
            $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
            $view->addExtension(new TwigExtension($container->get('router'), $basePath));
            $view->getEnvironment()->addFunction(new \Twig_Function('evalUrl', function($node) use ($container) {
                $url = $node->getPubInfo()->getCanonical();
                if (!isset($url) || $url == null) {
                    $section = $container->sitemap->getSection($node->getPubInfo()->getSectionPath());
                    $url = $section->getPubInfo()->getCanonical() . $node->getId() . '/' . preg_replace('/[^a-z0-9]+/', '-', strtolower($node->getTitle())) . '/index.html';
                }
                if (S::startsWith($url, '/')) {
                    $url = substr($url, 1);
                }
                return $url;
            }));
        
            return $view;
        };
    }

    public function setControllerHandler() {
        $this->container['PageController'] = function($container) {
            return new PageController($container);
        };
    }

    public function getApp() {
        return $this->app;
    }

}

if (!isset($autoload) || $autoload == null) {
    $settings = require_once('settings.php');
    $autoload = new Autoload($settings);
}
$app = $autoload->getApp();
