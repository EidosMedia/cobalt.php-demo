<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Commons\Exceptions\HttpClientException;

class AuthenticationController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function login($request, $response, $args) {
        try {
            $body = $request->getParsedBody();
            $this->container['session'] = $this->container['directoryService']->login($body['txtUsername'], $body['txtPassword']);

        } catch (\HttpClientException $ex) {
            $this->container['error'] = $this->parseResponseError($ex->getBody());
        
        } catch(\Exception $ex) {
            $this->container['error'] = $ex->getMessage();
        }

        return $this->renderPage($request, $response, $args);
    }

    public function logout($request, $response, $args) {
        try {
            $this->container['logout'] = $this->container['directoryService']->logout();

        } catch (\HttpClientException $ex) {
            $this->container['error'] = $this->parseResponseError($ex->getBody());

        } catch(\Exception $ex) {
            $this->container['error'] = $ex->getMessage();
        }

        return $this->renderPage($request, $response, $args);
    }

    private function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }

        return 'Unable to extract error message';
    }

    private function renderPage($request, $response, $args) {
        $pageController = new PageController($this->container);
        $args['path'] = '/';
        if (isset($args['error'])) {
            $pageController->renderError($request, $response, $args);
        }
        return $pageController->renderPageByPath($request, $response, $args);
    }

}