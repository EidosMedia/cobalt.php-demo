<?php

namespace App\Controller;

class PageController {

    private $view;

    public function __construct($container) {
        $this->container = $container;
    }

    public function renderPageByPath($request, $response, $args) {
        $path = $args['path'];
        return $this->renderPage('/', $request, $response, $args);
    }

    public function renderPageById($request, $response, $args) {
        $section = $args['section'];
        $id = $args['id'];
        $title = $args['title'];
        return $this->renderPage($id, $request, $response, $args);
    }

    private function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $siteName = $this->container->get('settings')['siteName'];
        $page = $this->container->siteService->getPage($siteName, $nodeOrIdOrPath);
        $currentObject = $page->getCurrentObject();
        $template = $currentObject->getType() . '.twig.html';
        if (!$this->container->view->getLoader()->exists($template)) {
            $template = $currentObject->getBaseType() . '.twig.html';
            if (!$this->container->view->getLoader()->exists($template)) {
                // TODO: 500 error, template not available
            }
        }
        return $this->container->view->render($response, $template, [
            'page' => $page,
            'sitemap' => $this->container->sitemap
        ]);
    }
}
