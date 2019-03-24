<?php

namespace App\Middlewares;

class AuthenticationMiddleware {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        if (!isset($_SESSION['logout'])) {
            $this->container->view->getEnvironment()->addGlobal('session', $_SESSION['session']);

        } else {
            var_dump($_SESSION['logout']);
        }

        $response = $next($request, $response);
        return $response;
    }

}
