<?php

namespace App\Services;

use App\Entity\Post;
use App\Store\PostStore;
use Framework\Store\SQLStore;
use Framework\Util\HtmlValidator;

use App\Entity\Role;
use App\Entity\User;

/**
 * Service som hanterar inlägg.
 */
class PostService
{

    private PostStore $postStore;
    private HtmlValidator $htmlValidator;

    function __construct(PostStore $postStore, HtmlValidator $htmlValidator)
    {
        $this->postStore = $postStore;
        $this->htmlValidator = $htmlValidator;
    }

    /**
     * Skapar ett nytt inlägg.
     * @param string $threadId Tråden som inlägget ska publiceras i.
     * @param string $author ID för användaren som publicerade inlägget.
     * @param string $content HTML-innehållet för inlägget.
     * @return bool Om inlägget skapades.
     */
    function createPost(string $threadId, string $author, string $content): bool
    {
        if(!$this->htmlValidator->validate($content))
            return false;

        $time = round(microtime(true) * 1000);
        $post = new Post(SQLStore::generateUuid(), $author, $threadId, $content, $time,
            $time, count($this->postStore->getPostsByThread($threadId)));

        $this->postStore->create($post);
        return true;
    }

    /**
     * Tar bort ett inlägg.
     * @param string $postId Inläggets ID.
     * @param User $user Användaren som publicerade inlägget.
     * @return bool Om inlägget togs bort.
     */
    function deletePost(string $postId, User $user): bool {
        if($user->role == Role::ADMIN){
            return $this->postStore->deleteById($postId);
        } else {
            return $this->postStore->deleteByAuthor($postId, $user->id);
        }
    }

    /**
     * @param string $threadId Trådens ID.
     * @return array Array med inlägg som finns i tråden.
     */
    function getPostsByThread(string $threadId): array {
        return $this->postStore->getPostsByThread($threadId);
    }

    /**
     * Uppdaterar ett inlägg.
     * @param string $postId Inläggets ID.
     * @param string $userId ID på den autentiserade användaren.
     * @param string $body Inläggets nya text.
     * @return bool Om inlägget kunde uppdateras.
     */
    public function updatePost(string $postId, string $userId, string $body): bool
    {
        if(!$this->htmlValidator->validate($body))
            return false;

        $post = $this->postStore->getPostById($postId);
        $post->content = $body;
        $post->timeUpdated = round(microtime(true) * 1000);
        return $this->postStore->update($post, $userId);
    }

}