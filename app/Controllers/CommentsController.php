<?php

namespace App\Controllers;

use Eidosmedia\Cobalt\Comments\Entities\Post;
use Eidosmedia\Cobalt\Comments\Entities\PostOptions;

class CommentsController {

    private $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function listPosts($request, $response, $args) {
        $context = null;

        try {
            $postOptions = new PostOptions();
            $postOptions->setStatusId('ACTIVE');
            $postOptions->setExternalObjectId($args['id']);
            $context['posts'] = $this->container['commentsService']->listPosts($postOptions);

        } catch (Exception $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        $this->handleError($request, $response, $args, $context);
        $args['posts'] = $context['posts'];
        return $this->renderPage($request, $response, $args);
    }

    public function addPost($request, $response, $args) {
        $body = $request->getParsedBody();
        $post = $this->preparePost($body, $args);
        $context = null;
        
        try {
            $context['post'] = $this->container['commentsService']->createPost($post);

        } catch (Exception $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        $this->handleError($request, $response, $args, $context);
        $args['post'] = $context['post'];
        return $this->renderPage($request, $response, $args);
    }

    public function updatePost($request, $response, $args) {
        $body = $request->getParsedBody();
        $post = $this->preparePost($body, $args);
        $context = null;
        
        try {
            $context['post'] = $this->container['commentsService']->updatePost($post);

        } catch (Exception $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        $this->handleError($request, $response, $args, $context);
        return $this->renderPage($request, $response, $context);
    }

    public function deletePost($request, $response, $args) {
        $body = $request->getParsedBody();
        $post = $this->preparePost($body, $args);
        $context = null;
        
        try {
            $context['post'] = $this->container['commentsService']->deletePost($post);

        } catch (Exception $ex) {
            $context['error'] = $this->parseResponseError($ex->getBody());
        }

        $this->handleError($request, $response, $args, $context);
        return $this->renderPage($request, $response, $context);
    }

    private function preparePost($body, $args) {
        $post = new Post();
        $post->setExternalObjectId($args['id']);
        $post->setDomainId($args['domainId']);
        $post->setForumId($args['forumId']);
        $post->setName($body['txtName']);
        $post->setDescription($body['txtDescription']);
        $post->setModerated(true);
        $post->setPinned(true);
        return $post;
    }

    private function handleError($request, $response, $args, $context) {
        if (isset($context['error'])) {
            // handle error here
        }
    }

    private function renderPage($request, $response, $args) {
        $pageController = new PageController($this->container);
        //$args['id'] = 'uuid';
        return $pageController->renderPageByPath($request, $response, $args);
    }

}