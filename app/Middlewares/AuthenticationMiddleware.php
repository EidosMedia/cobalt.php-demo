<?php

namespace App\Middlewares;

class AuthenticationMiddleware {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke($request, $response, $next) {
        if (isset($_SESSION['session'])) {
                $this->container->view->getEnvironment()->addGlobal('session', $_SESSION['session']);
        }

        $response = $next($request, $response);
        return $response;
    }

}
