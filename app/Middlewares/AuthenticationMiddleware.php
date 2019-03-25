<?php

namespace App\Middlewares;

class AuthenticationMiddleware {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        if (isset($_SESSION['sessionUserData'])) {
            $sessionUserData = unserialize($_SESSION['sessionUserData']);
            $this->container->view->getEnvironment()->addGlobal('session', $sessionUserData->getSession());
            $this->container->view->getEnvironment()->addGlobal('user', $sessionUserData->getUser());
        }

        $response = $next($request, $response);
        return $response;
    }

}
