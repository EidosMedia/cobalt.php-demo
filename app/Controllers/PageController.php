<?php

namespace App\Controllers;

class PageController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function renderPageByPath($request, $response, $args) {
        $path = $args['path'];
        return $this->renderPage($path, $request, $response, $args);
    }

    public function renderPageById($request, $response, $args) {
        $id = $args['id'];
        $commentsController = new CommentsController($this->container);
        $args['posts'] = $commentsController->listPosts($request, $response, $args);
        return $this->renderPage($id, $request, $response, $args);
    }

    public function renderError($request, $response, $args) {
        return $this->renderPage('/error', $request, $response, $args);
    }

    private function getPageType($page, $args) {
        $currentObject = $page->getCurrentObject();
        $template = $currentObject->getSys()->getType() . '.twig.html';
        if (!$this->container->view->getLoader()->exists($template)) {

            $template = $currentObject->getSys()->getBaseType() . '.twig.html';
            if (!$this->container->view->getLoader()->exists($template)) {
                // 500 error - template not available
                $template = 'error.twig.html';
                $args['error'] = 'Template not found for type ' . $currentObject->getSys()->getType();
            }
        }

        return $template;
    }

    public function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $page = $this->container->siteService->getPage($nodeOrIdOrPath);
        $template = $this->getPageType($page, $args);

        if (isset($args['error'])) {
            $template = 'error.twig.html';
            $context['error'] = $args['error'];
            return $this->container->view->render($response, $template, $context);
        }

        $context = [
            'page' => $page,
            'sitemap' => $this->container->sitemap
        ];

        if (isset($args['session'])) {
            $context['session'] = $args['session'];
        }

        return $this->container->view->render($response, $template, $context);
    }

}