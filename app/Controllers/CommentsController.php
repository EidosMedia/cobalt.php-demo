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
        $body = $request->getParsedBody();
        $post = $this->preparePost($body, $args);

        try {
            $this->cobaltServices->getCommentsService()->createPost($post);

        } catch (\Exception $ex) {
            $_SESSION['error'] = $this->parseResponseError($ex->getBody());
        }

        return $this->renderPage($request, $response, $args);
    }

    private function preparePost($body, $args) {
        $post = new Post();
        $post->setExternalObjectId($args['id']);
        $post->setDomainExternalObjectId($args['id']);
        $post->setForumExternalObjectId($args['id']);
        $post->setContent($body['txtPostContent']);
        return $post;
    }

    private function renderPage($request, $response, $args) {
        $pageController = new PageController($this->container);
        foreach ($args as $key => $value) {
            $this->container[$key] = $value;
        }
        $this->container['path'] = '/';
        return $pageController->renderPageByPath($request, $response, $this->container);
    }

}