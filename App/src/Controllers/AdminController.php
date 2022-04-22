<?php

namespace App\Controllers;

use App\Entity\User;
use App\Requests\Admin\UpdateUserRequest;
use Framework\Controller\Controller;
use Framework\Controller\Response\RedirectResponse;
use Framework\Controller\Response\ViewResponse;
use Framework\HTTP\HttpRequest;
use App\Services\BoardService;
use App\Services\UserService;
use App\Services\ThreadService;
use App\Requests\Admin\CreateBoardRequest;
use Framework\Util\StatusCode;

/**
 * Controller med vyer och API:er som endast ska kunna ses/användas i administratörssyften.
 */
class AdminController extends Controller
{

    private BoardService $boardService;
    private UserService $userService;
    private ThreadService $threadService;

    function __construct(BoardService $boardService,
                         UserService $userService,
                         ThreadService $threadService)
    {
        $this->boardService = $boardService;
        $this->userService = $userService;
        $this->threadService = $threadService;
    }

    function indexPage(): ViewResponse
    {
        return parent::view("admin/index");
    }

    function boardListPage(): ViewResponse
    {
        return parent::view("admin/boards", [
            "boards" => $this->boardService->getBoards()
        ]);
    }

    function userListPage(): ViewResponse
    {
        return parent::view("admin/users", [
            "users" => $this->userService->getUsers()
        ]);
    }

    function createBoard(CreateBoardRequest $request): RedirectResponse
    {
        $err = $request->validate();
        if($err != null)
            return parent::redirect("/admin/boards?status=0");

        $this->boardService->createBoard($request->name);
        return parent::redirect("/admin/boards");
    }

    function deleteBoard(HttpRequest $request): int
    {
        if($this->boardService->deleteBoard($request->params["id"])) {
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::NotFound);
        }
    }

    function deleteThread(HttpRequest $request): int
    {
        if($this->threadService->deleteThread($request->params["id"])) {
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::NotFound);
        }
    }

    function listUsers(): array
    {
        return $this->userService->getUsers();
    }

    function updateUser(UpdateUserRequest $request): int
    {
        $id = $request->request->params["id"];
        if($request->validate() != null)
            return parent::status(StatusCode::BadRequest);

        $updates = function(User $user) use ($request) { // Closure funktion som tar den nuvarande användaren och utför ändringar.
            $user->role = $request->role;
            $user->name = $request->name;
            return $user;
        };

        if($this->userService->updateUser($id, $updates)){
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::NotFound);
        }
    }

    function deleteUser(HttpRequest $request): int
    {
        if($this->userService->deleteUser($request->params["id"])) {
            return parent::status(StatusCode::OK);
        } else {
            return parent::status(StatusCode::NotFound);
        }
    }

}