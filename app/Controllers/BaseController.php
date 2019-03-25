<?php

namespace App\Controllers;

class BaseController {

    protected $container;
    private static $cobaltServices;

    public function __construct($container) {
        $this->container = $container;
    }

    public function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }

        return 'Unable to extract error message';
    }

    public function getCobaltServices($settings) {
        return $this->container->CobaltService;
    }

}