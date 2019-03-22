<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Comments\Entities\Post;

class CommentsController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function addPost($request, $response, $args) {
        $body = $request->getParsedBody();
        $post = $this->preparePost($body, $args);

        try {
            $this->container['directoryService']->login('admin', 'admin');
            $this->container['commentsService']->createPost($post);

        } catch (\Exception $ex) {
            $this->container['error'] = $this->parseResponseError($ex->getBody());
        }

        return $this->renderPage($request, $response, $args);
    }

    private function parseResponseError($body) {
        if (isset($body)) {
            return json_decode($body, true)['error']['message'];
        }

        return 'Unable to extract error message';
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