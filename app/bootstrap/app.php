<?php

use App\Controllers\AuthenticationController;
use App\Controllers\CommentsController;
use App\Controllers\PageController;
use App\Middlewares\AuthenticationMiddleware;
use App\Services\CobaltService;
use Slim\App;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Stringy\StaticStringy as S;
use Twig\Extension\DebugExtension;

use Eidosmedia\Cobalt\Directory\Entities\SessionUserData;

session_start();
$_SESSION['error'] = null;

require_once(__DIR__ . '/../../vendor/autoload.php');

$app = new App(['settings' => $settings]);

$_SESSION['settings'] = $settings;

// set routes for HTTP GET
foreach ($settings['routes']['get'] as $pattern => $controllerMethod) {
    $app->get($pattern, $controllerMethod)->setName($controllerMethod);
}

// set routes for HTTP POST
foreach ($settings['routes']['post'] as $pattern => $controllerMethod) {
    $app->post($pattern, $controllerMethod)->setName($controllerMethod);
}

$container = $app->getContainer();

$container['view'] = function($container) {
    $view = new Twig($container->get('settings')['templatePath'], [
        'cache' => false,
        'debug' => true
    ]);

    $view->addExtension(new DebugExtension());

    $view->addExtension(new TwigExtension($container->router, $container->request->getUri()));

    $view->getEnvironment()->addFunction(new \Twig_Function('evalUrl', function($node) use ($container) {
        $url = $node->getPubInfo()->getCanonical();
        if (!isset($url) || $url == null) {
            $section = $container->CobaltService->getSitemap()->getSection($node->getPubInfo()->getSectionPath());
            $url = $section->getPubInfo()->getCanonical() . $node->getId() . '/' . preg_replace('/[^a-z0-9]+/', '-', strtolower($node->getTitle())) . '/index.html';
        }
        if (S::startsWith($url, '/')) {
            $url = substr($url, 1);
        }
        return $url;
    }));

    return $view;
};

$container['CobaltService'] = new CobaltService($settings);

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
