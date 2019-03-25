<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Comments\Entities\PostOptions;
use App\Controllers\BaseController;

class PageController extends BaseController {

    protected $cobaltServices;

    public function __construct($container) {
        parent::__construct($container);
        $this->cobaltServices = $this->getCobaltServices($container->get('settings'));
    }

    public function renderPageByPath($request, $response, $args) {
        return $this->renderPage($args['path'], $request, $response, $args);
    }

    public function renderPageById($request, $response, $args) {
        try {
            $postOptions = new PostOptions();
            $postOptions->setExternalObjectId($args['id']);
            $postOptions->setLimit(3);
            $this->container['posts'] = $this->cobaltServices->getCommentsService()->listPosts($postOptions);

        } catch (\HttpClientException $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());

        } catch (\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        return $this->renderPage($args['id'], $request, $response, $args);
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
                $_SESSION['error'] = 'Template not found for type ' . $currentObject->getSys()->getType();
            }
        }

        return $template;
    }

    public function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $page = $this->cobaltServices->getSiteService($_SESSION['settings']['siteName'])->getPage($nodeOrIdOrPath);
        $template = $this->getPageType($page, $args);

        if (isset($args['error'])) {
            $template = 'error.twig.html';
            $this->container['error'] = $args['error'];
            return $this->container->view->render($response, $template, $this->container);
        }

        $context = [
            'page' => $page,
            'sitemap' => $this->cobaltServices->getSiteService($_SESSION['settings']['siteName'])->getSitemap()
        ];
        $this->container['sitemap'] = $context['sitemap'];

        if (isset($this->container['posts'])) {
            $context['posts'] = $this->container['posts'];
        }

        return $this->container->view->render($response, $template, $context);
    }

}