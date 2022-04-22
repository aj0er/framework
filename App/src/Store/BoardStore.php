<?php

namespace App\Store;

use App\Entity\Board;
use Framework\Mapping\ObjectMapper;
use Framework\Store\SQLiteStore;

class BoardStore extends SQLiteStore
{

    function __construct(ObjectMapper $mapper)
    {
        parent::__construct($mapper, "resources/database.sqlite", "boards", Board::class);
        parent::createTable([
            "id" => "binary(16)",
            "name" => "varchar(32)"
        ]);
    }

    public function create(Board $board){
        parent::insert($board);
    }

    public function getAllBoards(): array
    {
        return parent::query("SELECT * FROM boards");
    }

    public function deleteBoard(string $id): bool
    {
        $stmt = parent::execute("DELETE FROM boards WHERE id=:id", [":id" => $id]);
        return $stmt->rowCount() > 0;
    }

    public function getBoardById(string $id): ?Board
    {
        return parent::queryOne("SELECT * FROM boards WHERE id=:id", [":id" => $id]);
    }

}