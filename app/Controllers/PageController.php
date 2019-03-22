<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Comments\Entities\PostOptions;

class PageController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function renderPageByPath($request, $response, $args) {
        return $this->renderPage($args['path'], $request, $response, $args);
    }

    public function renderPageById($request, $response, $args) {
        try {
            $postOptions = new PostOptions();
            $postOptions->setExternalObjectId($args['id']);
            $postOptions->setLimit(3);
            $this->container['directoryService']->login('admin', 'admin');
            $this->container['posts'] = $this->container['commentsService']->listPosts($postOptions);

        } catch (\Exception $ex) {
            $this->container['error'] = $this->parseResponseError($ex->getBody());
        }

        return $this->renderPage($args['id'], $request, $response, $args);
    }

    public function renderError($request, $response, $args) {
        return $this->renderPage('/error', $request, $response, $args);
    }

    private function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }

        return 'Unable to extract error message';
    }

    private function getPageType($page, $args) {
        $currentObject = $page->getCurrentObject();
        $template = $currentObject->getSys()->getType() . '.twig.html';
        if (!$this->container->view->getLoader()->exists($template)) {

            $template = $currentObject->getSys()->getBaseType() . '.twig.html';
            if (!$this->container->view->getLoader()->exists($template)) {
                // 500 error - template not available
                $template = 'error.twig.html';
                $this->container['error'] = 'Template not found for type ' . $currentObject->getSys()->getType();
            }
        }

        return $template;
    }

    public function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $page = $this->container->siteService->getPage($nodeOrIdOrPath);
        $template = $this->getPageType($page, $args);

        if (isset($args['error'])) {
            var_dump($args['error']);
            exit(0);
            $template = 'error.twig.html';
            $this->container['error'] = $args['error'];
            return $this->container->view->render($response, $template, $this->container);
        }

        $context = [
            'page' => $page,
            'sitemap' => $this->container->sitemap
        ];

        if (isset($this->container['posts'])) {
            $context['posts'] = $this->container['posts'];
        }

        return $this->container->view->render($response, $template, $context);
    }

}