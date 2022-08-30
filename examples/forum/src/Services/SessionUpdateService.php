<?php

namespace App\Services;

use App\Middlewares\UserSessionHelperMiddleware;

/**
 * Service som cachar uppdaterade användare för att kunna uppdatera deras session.
 * Används b.la. av {@link UserSessionHelperMiddleware}
 */
class SessionUpdateService
{

    private array $updatedUsers;
    private string $path;

    function __construct(string $path){
        $this->path = $path;

        $data = @file_get_contents($path); // @ används för att gömma varningen om filen inte finns.
        if($data !== false){
            $this->updatedUsers = json_decode($data);
        } else {
            $this->updatedUsers = array();
        }
    }

    /**
     * @param $userId string Användarens ID.
     * @return bool Om användaren är borttagen.
     */
    function isUpdated(string $userId): bool
    {
        return in_array($userId, $this->updatedUsers);
    }

    /**
     * Tar bort användaren från cachen.
     * @param string $userId Användarens ID.
     */
    function clearUpdated(string $userId){
        $key = array_search($userId, $this->updatedUsers);
        if ($key !== false) {
            unset($this->updatedUsers[$key]);
        }

        $this->save();
    }

    /**
     * Lägger till användaren i cachen för borttagna användare.
     * @param $userId string Användarens ID.
     */
    function setUpdated(string $userId){
        array_push($this->updatedUsers, $userId);
        $this->save();
    }

    /**
     * Sparar cachen till fil.
     */
    private function save(){
        $values = array_values($this->updatedUsers); // Om användare tas bort från mitten av arrayen gör PHP den till ett objekt 
        file_put_contents($this->path, json_encode($values));
    }

}