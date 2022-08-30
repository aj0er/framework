<?php

namespace App\Controllers;

use App\Services\BoardService;
use Framework\Controller\Controller;
use Framework\Controller\Response\ViewResponse;

/**
 * Controller för allt relaterat till forumets tavlor.
 */
class BoardController extends Controller
{

    private BoardService $boardService;

    function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    function listBoards(): ViewResponse
    {
        return parent::view("board_list", ["boards" => $this->boardService->getBoards()]);
    }

}