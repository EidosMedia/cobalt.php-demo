<?php

namespace App\Services;

use Eidosmedia\Cobalt\CobaltSDK;
use Eidosmedia\Cobalt\Directory\Entities\SessionUserData;

class CobaltService {

    protected $sdk;
    protected $siteService;
    protected $sitemap;
    protected $directoryService;
    protected $commentsService;

    public function __construct($settings) {
        $this->sdk = new CobaltSDK($settings['discoveryUri'], $settings['tenant'], $settings['realm']);
        $this->siteService = $this->sdk->getSiteService($settings['siteName']);
        $this->sitemap = $this->siteService->getSitemap();
        $this->directoryService = $this->sdk->getDirectoryService();
        $this->commentsService = $this->sdk->getCommentsService();

        if (isset($_SESSION) && isset($_SESSION['sessionUserData'])) {
            $this->sdk->getAuthContext()->pushAuth(unserialize($_SESSION['sessionUserData']));
        }
    }

    public function getSDK() {
        return $this->sdk;
    }

    public function getSiteService() {
        return $this->siteService;
    }

    public function getSitemap() {
        return $this->sitemap;
    }

    public function getDirectoryService() {
        return $this->directoryService;
    }

    public function getCommentsService() {
        return $this->commentsService;
    }

}
