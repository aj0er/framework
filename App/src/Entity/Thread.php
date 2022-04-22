<?php

namespace App\Entity;

/**
 * En tråd som publiceras på en tavla.
 */
class Thread
{

    /**
     * @var string Trådens ID.
     */
    public string $id;
    /**
     * @var string Trådens namn.
     */
    public string $name;
    /**
     * @var string ID för användaren som skapade tråden.
     */
    public string $author;
    /**
     * @var int Tidsstämpel för då tråden skapades.
     */
    public int $timeCreated;
    /**
     * @var string ID för tavlan som tråden är skapad på.
     */
    public string $board;

    /**
     * @var User|null Användaren som skapade inlägget, mappas från $author.
     */
    public ?User $user;

    /**
     * @param string $id Trådens ID.
     * @param string $name Trådens namn.
     * @param string $author ID för användaren som skapade tråden.
     * @param int $timeCreated Tidsstämpel för då tråden skapades.
     * @param string $board ID för tavlan som tråden är skapad på.
     */
    public function __construct(string $id, string $name, string $author, int $timeCreated, string $board){
        $this->id = $id;
        $this->name = $name;
        $this->author = $author;
        $this->timeCreated = $timeCreated;
        $this->board = $board;
    }

    /**
     * @return string Formatterad tidsstämpel i formatet 2022-03-12 10:34
     */
    function getDateCreatedFormatted(): string {
        return date("Y-m-d H:i", (int) ($this->timeCreated / 1000.0));
    }

}