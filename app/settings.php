<?php

$discoveryUri = 'http://localhost:8480/discovery';
if (getenv('CPD_DISCOVERYURI')) {
    $discoveryUri = getenv('CPD_DISCOVERYURI');
}

$siteName = 'test-site';
if (getenv('CPD_SITENAME')) {
    $siteName = getenv('CPD_SITENAME');
}

$routes = [
    '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/index.html' => 'PageController:renderPageById',
    '{path:.*}' => 'PageController:renderPageByPath'
];

$settings = [
    'debug' => true,
    'displayErrorDetails' => true,
    'templatePath' => '../templates/',
    'discoveryUri' => $discoveryUri,
    'siteName' => $siteName,
    'routes' => $routes
];

return $settings;
