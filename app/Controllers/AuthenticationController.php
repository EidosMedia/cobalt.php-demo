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
            $_SESSION['session'] = [
                'user' => $body['txtUsername'],
                'password' => $body['txtPassword'],
                'token' => $this->directoryService->login($body['txtUsername'], $body['txtPassword'])->getSession()->getId()
            ];

        } catch (\HttpClientException $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());
        
        } catch(\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        return $this->renderPage($request, $response, $args);
    }

    public function logout($request, $response, $args) {
        try {
            $this->directoryService->logout();
            session_destroy();

        } catch (\HttpClientException $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());

        } catch(\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        return $this->renderPage($request, $response, $args);
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