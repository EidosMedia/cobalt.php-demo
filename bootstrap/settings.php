<?php

$settings = [
    'discoveryUri' => getenv('CPD_DISCOVERYURI'),
    'siteName' => getenv('CPD_SITENAME')
];

return $settings;