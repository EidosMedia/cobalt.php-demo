<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AuthenticationController extends BaseController {

    protected $directoryService;

    public function __construct($container) {
        parent::__construct($container);
        $cobaltServices = $this->getCobaltServices($container->get('settings'));
        $this->directoryService = $cobaltServices->getDirectoryService();
    }

    public function login($request, $response, $args) {
        try {
            $body = $request->getParsedBody();
            $sessionUserData = $this->directoryService->login($body['txtUsername'], $body['txtPassword']);
            $_SESSION['sessionUserData'] = serialize($sessionUserData);
        } catch (\HttpClientException $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());
        } catch(\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor('PageController:renderPageByPath', ['path' => '']));
        //return $this->renderPage($request, $response, $args);
    }

    public function logout($request, $response, $args) {
        try {
            $this->directoryService->logout();
        } catch (\HttpClientException $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());
        } catch(\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        session_destroy();

        return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor('PageController:renderPageByPath', ['path' => '']));
        //return $this->renderPage($request, $response, $args);
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