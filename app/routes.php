<?php

$app->get('{section:.*}/{id:[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{12}-[a-zA-Z0-9]{4}}/{title:[a-zA-Z0-9\-_]+}/index.html', 'PageController:renderPageById');
$app->get('{path:.*}', 'PageController:renderPageByPath');
