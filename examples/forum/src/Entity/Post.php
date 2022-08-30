<?php

namespace App\Entity;

use Framework\Store\CustomDataObject;

/**
 * Ett inlägg som publiceras i en tråd.
 */
class Post extends CustomDataObject
{

    /**
     * @var string Inläggets ID.
     */
    public string $id;
    /**
     * @var string ID för användaren som publicerade inlägget.
     */
    public string $author;
    /**
     * @var string ID för tråden där inlägget publicerats i.
     */
    public string $thread;

    /**
     * @var string HTML-innehåll för inlägget.
     */
    public string $content;
    /**
     * @var int Tidsstämpel för då inlägget publicerades.
     */
    public int $timeCreated;
    /**
     * @var int Tidsstämpel för då inlägget senast uppdaterades.
     */
    public int $timeUpdated;
    /**
     * @var int Inläggets ID för den specifika tråden, 0 = första inlägget i tråden
     */
    public int $idx;

    /**
     * @var User|null Användaren som publicerat inlägget, mappas från $author.
     */
    public ?User $user;

    /**
     * @param string $id Inläggets ID.
     * @param string $author ID för användaren som publicerade inlägget.
     * @param string $thread ID för tråden där inlägget publicerats i.
     * @param string $content HTML-innehåll för inlägget.
     * @param int $timeCreated Tidsstämpel för då inlägget senast uppdaterades.
     * @param int $timeUpdated Tidsstämpel för då inlägget senast uppdaterades.
     * @param int $idx Inläggets ID för den specifika tråden, 0 = första inlägget i tråden
     */
    public function __construct(string $id, string $author, string $thread, string $content,
                                int $timeCreated, int $timeUpdated, int $idx)
    {
        $this->id = $id;
        $this->author = $author;
        $this->thread = $thread;
        $this->content = $content;
        $this->timeCreated = $timeCreated;
        $this->timeUpdated = $timeUpdated;
        $this->idx = $idx;
    }

    /**
     * @return string Formatterad tidsstämpel i formatet 2022-03-12 10:34:00
     */
    function getCreateTimeStampFormatted(): string {
        return date("Y-m-d H:i:s", (int) ($this->timeCreated / 1000.0));
    }

    /**
     * @return string Formatterad tidsstämpel i formatet 2022-03-12 10:34:00
     */
    function getEditTimeStampFormatted(): string {
        return date("Y-m-d H:i:s", (int) ($this->timeUpdated / 1000.0));
    }

    /**
     * @return bool Om inlägget har blivit uppdaterat sedan det först publicerades.
     */
    function isUpdated(): bool
    {
        return $this->timeUpdated != $this->timeCreated;
    }

}