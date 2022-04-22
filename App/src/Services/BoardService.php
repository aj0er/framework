<?php

namespace App\Services;

use App\Entity\Board;
use App\Store\BoardStore;
use Framework\Store\SQLStore;

/**
 * Service som hanterar tavlor.
 */
class BoardService
{

    private BoardStore $boardStore;

    function __construct(BoardStore $boardStore)
    {
        $this->boardStore = $boardStore;
    }

    /**
     * @return array Array med alla tavlor.
     */
    function getBoards(): array
    {
        return $this->boardStore->getAllBoards();
    }

    /**
     * @param $id string Tavlans ID.
     * @return Board|null Funnen tavla eller null om ingen finns.
     */
    function getBoardById(string $id): ?Board
    {
        return $this->boardStore->getBoardById($id);
    }

    /**
     * @param string $id Tavlans ID.
     * @return bool Om borttagandet lyckades.
     */
    function deleteBoard(string $id): bool
    {
        return $this->boardStore->deleteBoard($id);
    }

    /**
     * Skapar en ny tavla.
     * @param string $name Tavlans namn.
     */
    function createBoard(string $name)
    {
        $board = new Board(SQLStore::generateUuid(), $name);
        $this->boardStore->create($board);
    }

}