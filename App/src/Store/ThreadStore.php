<?php

namespace App\Store;

use App\Entity\Thread;
use Framework\Mapping\ObjectMapper;
use Framework\Store\SQLiteStore;

class ThreadStore extends SQLiteStore
{

    private UserStore $userStore;
    private PostStore $postStore;

    public function __construct(ObjectMapper $mapper, UserStore $userStore, PostStore $postStore)
    {
        parent::__construct($mapper, "resources/database.sqlite", "threads", Thread::class);

        $this->userStore = $userStore;
        $this->postStore = $postStore;

        $this->createTable([
            "id" => "binary(16)",
            "name" => "varchar(32)",
            "author" => "varchar(32)",
            "board" => "varchar(32)",
            "timeCreated" => "varchar(32)",
        ]);
    }

    public function create(Thread $thread)
    {
        parent::insert($thread);
    }

    public function getThreadById(string $id): ?Thread
    {
        return parent::queryOne("SELECT * FROM threads WHERE id=:id", [":id" => $id]);
    }

    public function getThreadsByBoard(string $board): array
    {
        return parent::queryNamed("SELECT * FROM threads WHERE board=:board ORDER BY timeCreated DESC", [":board" => $board]);
    }

    public function deleteThread(string $id): bool
    {
        $stmt = parent::execute("DELETE FROM threads WHERE id=:id", [":id" => $id]);

        if ($stmt->rowCount() > 0) {
            $this->postStore->deletePostsByThread($id);
            return true;
        } else {
            return false;
        }
    }

    protected function onFetched(mixed $data)
    {
        $data->user = $this->userStore->getUserById($data->author);
    }

}