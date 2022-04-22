<?php

namespace App\Controllers;

use App\Requests\Forum\CreateThreadRequest;
use App\Services\BoardService;
use App\Services\ThreadService;
use Framework\Controller\Controller;
use Framework\Controller\Response\RedirectResponse;
use Framework\Controller\Response\ViewResponse;
use Framework\HTTP\HttpRequest;
use Framework\Util\StatusCode;

/**
 * Controller för allt gällande trådar.
 */
class ThreadController extends Controller
{

    private BoardService $boardService;
    private ThreadService $threadService;

    function __construct(ThreadService $threadService,
                         BoardService  $boardService)
    {
        $this->threadService = $threadService;
        $this->boardService = $boardService;
    }

    function createThreadPage(HttpRequest $request): ViewResponse
    {
        $boardId = $request->params["id"];
        return parent::view("thread_view", [
            "boardId" => $boardId
        ]);
    }

    function getThreadsByBoard(HttpRequest $request): ViewResponse|RedirectResponse
    {
        $boardId = $request->params["id"];
        $board = $this->boardService->getBoardById($boardId);
        if ($board == null)
            return parent::redirect("/boards");

        return parent::view("thread_list", [
            "board" => $board,
            "threads" => $this->threadService->getThreadsByBoard($boardId)
        ]);
    }

    function createThread(CreateThreadRequest $request): int|RedirectResponse
    {
        $boardId = $request->request->params["id"];

        $error = $request->validate();
        if ($error != null)
            return parent::redirect("/boards/" . $boardId . "/createThread?status=0");

        $user = $_SESSION["user"];
        $thread = $this->threadService->createThreadAndPost($request->title, $user->id, $boardId, $request->content);
        if($thread != null) {
            return parent::redirect("/threads/" . $thread->id);
        } else {
            return parent::status(StatusCode::BadRequest);
        }
    }

}