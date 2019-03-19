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
    'get' => [
        '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/index.html' => 'PageController:renderPageById',
        '{path:.*}' => 'PageController:renderPageByPath',
        '{path:/error}' => 'PageController:renderError'
    ],
    'post' => [
        '{path:/login}' => 'AuthenticationController:login',
        '{path:/logout}' => 'AuthenticationController:logout',
        '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/comments' => 'CommentsController:listPosts',
        '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/comments/add' => 'CommentsController:addPost',
        '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/comments/update/{postId:.*}' => 'CommentsController:updatePost',
        '{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/comments/delete/{postId:.*}' => 'CommentsController:deletePost',
    ]
];

$settings = [
    'debug' => true,
    'displayErrorDetails' => true,
    'templatePath' => '../templates/',
    'discoveryUri' => $discoveryUri,
    'siteName' => $siteName,
    'routes' => $routes,
    'sessionExpirationTime' => 60 * 30
];

return $settings;
