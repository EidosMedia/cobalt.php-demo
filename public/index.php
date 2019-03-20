<?php

require_once(__DIR__ . '/../app/autoload.php');
if (!isset($autoload) || $autoload == null) {
    $settings = require_once(__DIR__ . '/../app/settings.php');
    $autoload = new App\Autoload($settings);
}
$app = $autoload->getApp();
$app->run();
