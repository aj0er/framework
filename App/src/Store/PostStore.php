<?php

namespace App\Store;

use App\Entity\Post;
use Framework\Mapping\ObjectMapper;
use Framework\Store\SQLiteStore;

class PostStore extends SQLiteStore {

    private UserStore $userStore;

    public function __construct(ObjectMapper $mapper, UserStore $userStore)
    {
        $this->userStore = $userStore;
    
        parent::__construct($mapper, "resources/database.sqlite", "posts", Post::class);
        $this->createTable([
            "id" => "binary(16)",
            "idx" => "int",
            "author" => "varchar(32)",
            "thread" => "varchar(32)",

            "content" => "varchar(32)",
            "timeCreated" => "varchar(32)",
            "timeUpdated" => "varchar(32)",
        ]);
    }

    public function create(Post $post){
        parent::insert($post);
    }

    public function update(Post $post, string $userId): bool {
        $stmt = parent::executeNamed("UPDATE posts SET content=:content, timeUpdated=:timeUpdated WHERE id=:oid AND author=:user", [
            ":oid" => $post->id,
            ":content" => $post->content,
            ":timeUpdated" => $post->timeUpdated,
            ":user" => $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function getPostsByThread(string $thread): array {
        return parent::queryNamed("SELECT * FROM posts WHERE thread=:thread", [":thread" => $thread]);
    }

    public function getPostById(string $id): ?Post {
        return parent::queryOne("SELECT * FROM posts WHERE id=:id", [":id" => $id]);
    }

    public function deleteById(string $postId): bool
    {
        $stmt = parent::executeNamed("DELETE FROM posts WHERE id=:id AND NOT idx=0", [":id" => $postId]);
        return $stmt->rowCount() > 0;
    }

    public function deleteByAuthor(string $postId, string $userId): bool
    {
        $stmt = parent::executeNamed("DELETE FROM posts WHERE id=:id AND NOT idx=0 AND author=:author", [":id" => $postId, ":author" => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function deletePostsByThread(string $id)
    {
        parent::executeNamed("DELETE FROM posts WHERE thread=:thread", [":thread" => $id]);
    }

    protected function onFetched(mixed $data){
        $data->user = $this->userStore->getUserById($data->author);
    }

}