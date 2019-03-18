<?php

require_once('../app/autoload.php');
if (!isset($autoload) || $autoload == null) {
    $settings = require_once('../app/settings.php');
    $autoload = new App\Autoload($settings);
}
$app = $autoload->getApp();
$app->run();
