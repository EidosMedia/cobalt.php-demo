<?php

namespace App\Controllers;

class AuthenticationController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($request, $response, $args) {
        $context = null;

        try {
            $body = $request->getParsedBody();
            $context['session'] = $this->container['directoryService']->login($body['txtUsername'], $body['txtPassword']);

        } catch (HttpClientException $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        
        } catch(Exception $ex) {
            $context['error'] = $ex->getMessage();
        }


        $this->handleError($request, $response, $args, $context);
        $args['session'] = $context['session'];
        return $this->renderPage($request, $response, $args);
    }

    public function logout($request, $response, $args) {
        $context = null;

        try {
            $context['logout'] = $this->container['directoryService']->logout();

        } catch (HttpClientException $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());

        } catch(Exception $ex) {
            $context['error'] = $ex->getMessage();
        }

        $this->handleError($request, $response, $args, $context);
        return $this->renderPage($request, $response, $args);
    }

    private function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }

        return 'Unable to extract error message';
    }

    private function handleError($request, $response, $args, $context) {
        if (isset($context['error'])) {
            // handle error here
        }
    }

    private function renderPage($request, $response, $args) {
        $pageController = new PageController($this->container);
        $args['path'] = '/';
        return $pageController->renderPageByPath($request, $response, $args);
    }

}