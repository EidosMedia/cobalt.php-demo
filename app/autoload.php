<?php

namespace App;

use App\Controller\AuthController;
use App\Controller\PageController;
use Eidosmedia\Cobalt\CobaltSDK;
use Eidosmedia\Cobalt\Commons\Exceptions\ServiceNotAvailableException;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Stringy\StaticStringy as S;

class Autoload {
    
    private $app = null;
    private $container = null;
    private $settings = null;
    private $sdk = null;
    private $siteService = null;
    private $sitemap = null;
    
    public function __construct($settings) {
        // load slim, twig and other dependencies only if autoload class is instanciated
        require_once('../vendor/autoload.php');
        $this->settings = $settings;

        // start slim/twig with all settings from settings.php
        $this->app = new App(['settings' => $settings]);
        $this->container = $this->app->getContainer();

        // set routes based on settings.php
        $this->setRoutes();

        $this->setViews();
        $this->setControllers();
        
        // initialise Cobalt SDK
        $this->initCobaltSDK();
    }

    public function setRoutes() {
        // set routes for HTTP GET
        foreach ($this->settings['routes']['get'] as $pattern => $controllerMethod) {
            $this->app->get($pattern, $controllerMethod);
        }

        // set routes for HTTP POST
        foreach ($this->settings['routes']['post'] as $pattern => $controllerMethod) {
            $this->app->post($pattern, $controllerMethod);
        }
    }

    public function setControllers() {
        $this->container['PageController'] = function($container) {
            return new PageController($container);
        };

        $this->container['AuthController'] = function($container) {
            return new AuthController($container);
        };
    }

    public function setViews() {
        $this->container['view'] = function($container) {
            $view = new Twig($container->get('settings')['templatePath'], [
                'cache' => false,
                'debug' => true
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

    public function initCobaltSDK() {
        try {
            $this->sdk = new CobaltSDK($this->settings['discoveryUri']);
            $this->siteService = $this->sdk->getSiteService($this->settings['siteName']);
            $this->sitemap = $this->siteService->getSitemap();
            $this->container['cobalt'] = $this->sdk;
            $this->container['siteService'] = $this->siteService;
            $this->container['sitemap'] = $this->sitemap;

        } catch (ServiceNotAvailableException $ex) {
            // TODO call error.twig.html
        }
    }

    public function getApp() {
        return $this->app;
    }

}
