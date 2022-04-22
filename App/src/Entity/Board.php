<?php

namespace App\Entity;

/**
 * En tavla som trådar skapas på.
 */
class Board {

    /**
     * @var string Trådens ID.
     */
    public string $id;
    /**
     * @var string Trådens namn.
     */
    public string $name;

    /**
     * @param string $id Trådens ID.
     * @param string $name Trådens namn.
     */
    function __construct(string $id, string $name){
        $this->id    = $id;
        $this->name  = $name;
    }

}