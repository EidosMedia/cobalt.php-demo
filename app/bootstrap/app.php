<?php

use App\Controllers\AuthenticationController;
use App\Controllers\CommentsController;
use App\Controllers\PageController;
use App\Middlewares\AuthenticationMiddleware;
use Eidosmedia\Cobalt\CobaltSDK;
use Eidosmedia\Cobalt\Directory\Entities\SessionUserData;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Stringy\StaticStringy as S;
use Twig\Extension\DebugExtension;

session_start();
require_once(__DIR__ . '/../../vendor/autoload.php');
$app = new App(['settings' => $settings]);

// set routes for HTTP GET
foreach ($settings['routes']['get'] as $pattern => $controllerMethod) {
    $app->get($pattern, $controllerMethod);
}

// set routes for HTTP POST
foreach ($settings['routes']['post'] as $pattern => $controllerMethod) {
    $app->post($pattern, $controllerMethod);
}

$container = $app->getContainer();
$container['view'] = function($container) {
    $view = new Twig($container->get('settings')['templatePath'], [
        'cache' => false,
        'debug' => true
    ]);

    $view->addExtension(new DebugExtension());

    $view->addExtension(new TwigExtension($container->router, $container->request->getUri()));

    $view->getEnvironment()->addGlobal('context', $container);

    $view->getEnvironment()->addFunction(new \Twig_Function('parseSession', function($sessionUserData) use ($container) {
        return new SessionUserData($sessionUserData);
    }));

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

$container['PageController'] = function($container) {
    return new PageController($container);
};

$container['AuthenticationController'] = function($container) {
    return new AuthenticationController($container);
};

$container['CommentsController'] = function($container) {
    return new CommentsController($container);
};

// login/logout middleware
$app->add(new AuthenticationMiddleware($container));

try {
    $tenant = (isset($tenant)) ? $tenant : null;
    $realm = (isset($realm)) ? $realm : null;
    $sdk = new CobaltSDK($settings['discoveryUri'], $tenant, $realm);
    $container['cobalt'] = $sdk;
    $container['siteService'] = $sdk->getSiteService($settings['siteName']);
    $container['sitemap'] = $container['siteService']->getSitemap();
    $container['directoryService'] = $sdk->getDirectoryService();
    $container['commentsService'] = $sdk->getCommentsService();

} catch (\ServiceNotAvailableException $ex) {
    // TODO call error.twig.html

} catch (\Exception $ex) {
    // TODO call error.twig.html
}