<?php

namespace App\Controller;

use Eidosmedia\Cobalt\Commons\Exceptions\HttpClientException;

class AuthController {

    private $container = null;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($request, $response, $args) {
        $context = [];
        try {
            $body = $request->getParsedBody();
            $context['session'] = $this->container->cobalt->getDirectoryService()->login($body['txtUsername'], $body['txtPassword']);

        } catch (HttpClientException $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        return $this->renderPage('/', $request, $response, $context);
    }

    public function logout($request, $response, $args) {
        $context = [];
        try {
            $this->container->cobalt->getDirectoryService()->logout();
            if (isset($context['session'])) {
                unset($context['session']);
            }

        } catch (HttpClientException $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        return $this->renderPage('/', $request, $response, $context);
    }

    private function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }
        return null;
    }

    private function renderPage($nodeOrIdOrPath, $request, $response, $args) {
        $page = $this->container->siteService->getPage($nodeOrIdOrPath);
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

        $context = [
            'page' => $page,
            'sitemap' => $this->container->sitemap
        ];

        if (isset($args['session'])) {
            $context['session'] = $args['session'];
        }

        if (isset($args['error'])) {
            $template = 'error.twig.html';
            $context['error'] = $args['error'];
        }

        return $this->container->view->render($response, $template, $context);
    }

}