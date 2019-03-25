<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Comments\Entities\Post;
use App\Controllers\BaseController;

class CommentsController extends BaseController {

    protected $cobaltServices;

    public function __construct($container) {
        parent::__construct($container);
        $this->cobaltServices = $this->getCobaltServices($container->get('settings'));
    }

    public function addPost($request, $response, $args) {
        $sitemap = $this->cobaltServices->getSitemap();
        $siteId = $sitemap->getRoot()->getId();

        $body = $request->getParsedBody();
        $post = new Post();
        $post->setExternalObjectId($args['id']);
        $post->setDomainExternalObjectId($siteId);
        $post->setForumExternalObjectId($siteId);
        $post->setContent($body['txtPostContent']);

        try {
            $post = $this->cobaltServices->getCommentsService()->createPost($post);
        } catch (\Exception $ex) {
            $_SESSION['error'] = $ex->getMessage();
        }

        return $response->withStatus(302)->withHeader('Location', $this->container->router->pathFor('PageController:renderPageById', $args));
    }

}