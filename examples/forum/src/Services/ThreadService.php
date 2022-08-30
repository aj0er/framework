<?php

namespace App\Services;

use App\Entity\Thread;
use App\Store\ThreadStore;
use Framework\Store\SQLStore;

/**
 * Service som hanterar trådar.
 */
class ThreadService
{

    private ThreadStore $threadStore;
    private PostService $postService;

    function __construct(ThreadStore $threadStore, PostService $postService)
    {
        $this->threadStore = $threadStore;
        $this->postService = $postService;
    }

    /**
     * Försöker skapar en ny tråd och ett tillhörande första inlägg.
     * @param string $title Trådens titel.
     * @param string $userId ID på användaren som försöker skapa tråden.
     * @param string $boardId ID på tavlan som tråden ska skapas på.
     * @param string $postContent Inläggets HTML-innehåll.
     * @return Thread|null Nyskapad tråd eller null om något misslyckades.
     */
    public function createThreadAndPost(string $title, string $userId, string $boardId, string $postContent): ?Thread
    {
        $timestamp = round(microtime(true) * 1000);

        $thread = new Thread(SQLStore::generateUuid(), $title, $userId, $timestamp, $boardId);
        if(!$this->postService->createPost($thread->id, $userId, $postContent)){
            return null;
        }

        $this->threadStore->create($thread);
        return $thread;
    }

    /**
     * @param string $threadId Trådens ID.
     * @return Thread|null Funnen tråd eller null om ingen fanns med det ID:et.
     */
    public function getThreadById(string $threadId): ?Thread {
        return $this->threadStore->getThreadById($threadId);
    }

    /**
     * @param string $boardId Tavlans ID.
     * @return array Array med trådar som finns på en viss tavla.
     */
    public function getThreadsByBoard(string $boardId): array
    {
        return $this->threadStore->getThreadsByBoard($boardId);
    }

    /**
     * Tar bort en tråd.
     * @param string $id Tavlans ID.
     */
    function deleteThread(string $id): bool
    {
        return $this->threadStore->deleteThread($id);
    }

}