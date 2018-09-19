<?php

$settings = require __DIR__ . '/settings.php';

use Stringy\StaticStringy as S;

$app = new \Slim\App(['settings' => $settings]);
$container = $app->getContainer();

// init cobalt SDK
$cobalt = new \Eidosmedia\Cobalt\Cobalt($container->get('settings')['discoveryUri']);
$siteService = $cobalt->getSite();
$container['cobalt'] = $cobalt;
$container['siteService'] = $siteService;
$container['sitemap'] = $siteService->getSitemap($container->get('settings')['siteName']);

// init template engine
$container['view'] = function($container) {
    $view = new \Slim\Views\Twig('../templates', [
        'cache' => false
    ]);
    $basePath = rtrim(str_ireplace('index.php', '', $container->get('request')->getUri()->getBasePath()), '/');
    $view->addExtension(new \Slim\Views\TwigExtension($container->get('router'), $basePath));

    $view->getEnvironment()->addFunction(new \Twig_Function('evalUrl', function($node) use ($container) {
        $url = $node->getCanonical();
        if (!isset($url) || $url == null) {
            $section = $container->sitemap->getSection($node->getSectionPath());
            $url = $section->getCanonical() . $node->getId() . '/' . preg_replace('/[^a-z0-9]+/', '-', strtolower($node->getTitle())) . '/index.html';
        }
        if (S::startsWith($url, '/')) {
            $url = substr($url, 1);
        }
        return $url;
    }));

    return $view;
};

$container['PageController'] = function($container) {
    return new \App\Controller\PageController($container);
};
