<?php

namespace App\Controller;

class PageController {

    private $container = null;

    public function __construct($container) {
        $this->container = $container;
    }

    public function renderPageByPath($request, $response, $args) {
        $path = $args['path'];
        return $this->renderPage($path, $request, $response, $args);
    }

    public function renderPageById($request, $response, $args) {
        $id = $args['id'];
        return $this->renderPage($id, $request, $response, $args);
    }

    private function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $page = $this->container->siteService->getPage($nodeOrIdOrPath);
        $currentObject = $page->getCurrentObject();
        $template = $currentObject->getSys()->getType() . '.twig.html';
        if (!$this->container->view->getLoader()->exists($template)) {
            $template = $currentObject->getSys()->getBaseType() . '.twig.html';
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