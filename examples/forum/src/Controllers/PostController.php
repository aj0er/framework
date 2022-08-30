<?php

namespace App\Controllers;

use App\Services\PostService;
use App\Services\ThreadService;
use Framework\Controller\Controller;
use Framework\Controller\Response\RedirectResponse;
use Framework\Controller\Response\ViewResponse;
use Framework\HTTP\HttpRequest;
use Framework\Util\StatusCode;

/**
 * Controller för allt gällande inlägg.
 */
class PostController extends Controller
{

    private PostService $postService;
    private ThreadService $threadService;

    function __construct(PostService   $postService,
                         ThreadService $threadService)
    {
        $this->postService = $postService;
        $this->threadService = $threadService;
    }

    function createPost(HttpRequest $request): RedirectResponse
    {
        $user = $_SESSION["user"];
        $threadId = $request->params["id"];
        $content = $request->query["content"];

        if($content != null && strlen($content) > 0) {
            $this->postService->createPost($threadId, $user->id, $content);
        }

        return parent::redirect("/threads/" . $threadId);
    }

    function deletePost(HttpRequest $request): int
    {
        $postId = $request->params["id"];
        $user = $_SESSION["user"];

        if ($this->postService->deletePost($postId, $user)) {
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::NotFound);
        }
    }

    function updatePost(HttpRequest $request): int
    {
        $postId = $request->params["id"];
        $user = $_SESSION["user"];

        $content = $request->body;

        if(strlen($content) < 1)
            return parent::status(StatusCode::BadRequest);

        if($this->postService->updatePost($postId, $user->id, $content)) {
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::BadRequest);
        }
    }

    function postListPage(HttpRequest $request): ViewResponse|int
    {
        $threadId = $request->params["id"];
        $thread = $this->threadService->getThreadById($threadId);

        $posts = $this->postService->getPostsByThread($threadId);
        if($thread == null || count($posts) < 1){
            return parent::status(StatusCode::NotFound);
        }

        usort($posts, function ($a, $b) {
            return $a->timeCreated <=> $b->timeCreated;
        });

        return parent::view("thread_view", [
            "boardId" => $thread->board,
            "thread" => $thread,
            "posts" => $posts
        ]);
    }

}