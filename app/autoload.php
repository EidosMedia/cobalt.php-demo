<?php

namespace App;

use App\Controllers\AuthenticationController;
use App\Controllers\CommentsController;
use App\Controllers\PageController;
use Eidosmedia\Cobalt\CobaltSDK;
use Eidosmedia\Cobalt\Comments\Entities\PostOptions;
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

        $this->container['AuthenticationController'] = function($container) {
            return new AuthenticationController($container);
        };

        $this->container['CommentsController'] = function($container) {
            return new CommentsController($container);
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
            $view->addExtension(new \Twig\Extension\DebugExtension());
            $view->getEnvironment()->addGlobal('context', $container);
            
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

    public function initCobaltSDK($tenant = null, $realm = null) {
        try {
            $this->sdk = new CobaltSDK($this->settings['discoveryUri'], $tenant, $realm);
            $this->container['cobalt'] = $this->sdk;
            $this->container['siteService'] = $this->sdk->getSiteService($this->settings['siteName']);
            $this->container['sitemap'] = $this->container['siteService']->getSitemap();
            $this->container['directoryService'] = $this->sdk->getDirectoryService();
            $this->container['directoryService']->login('admin', 'admin');
            $this->container['commentsService'] = $this->sdk->getCommentsService();

        } catch (ServiceNotAvailableException $ex) {
            // TODO call error.twig.html

        } catch (Exception $ex) {
            // TODO call error.twig.html
        }
    }

    public function getApp() {
        return $this->app;
    }

}
