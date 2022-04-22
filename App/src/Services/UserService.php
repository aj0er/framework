<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\Role;
use App\Store\UserStore;
use Closure;
use Framework\Store\SQLStore;

/**
 * Service som hanterar användare.
 */
class UserService
{

    private UserStore $userStore;
    private SessionUpdateService $sessionUpdateService;

    function __construct(UserStore $userStore, SessionUpdateService $sessionUpdateService)
    {
        $this->userStore = $userStore;
        $this->sessionUpdateService = $sessionUpdateService;
    }

    /**
     * Försöker autentisera en användare med den specificerade e-postadressen och lösenordet.
     * Användaren måste finnas samt måste även lösenorden stämma överens för att kunna autentiseras.
     * @param string $email Användarens e-postadress.
     * @param string $password Användarens lösenord.
     * @return User|null Den autentiserade användaren eller null om autentiseringen misslyckades.
     */
    function authenticate(string $email, string $password): ?User
    {
        $user = $this->getUserByEmail($email);
        if ($user == null || !password_verify($password, $user->password))
            return null;

        return $user;
    }

    /**
     * Försöker skapa en ny användare.
     * @param string $email Användarens e-postadress.
     * @param string $password Användarens lösenord.
     * @return User|null Nyskapad användare eller null om det misslyckades.
     */
    function createUser(string $email, string $password): ?User
    {
        if($this->getUserByEmail($email) != null) // En användare med e-postadressen finns redan.
            return null;

        $user = new User(SQLStore::generateUuid(), strtolower($email), null,
            password_hash($password, PASSWORD_DEFAULT), Role::USER);

        $this->userStore->create($user);
        return $user;
    }

    /**
     * Försöker sätta en användares användarnamn.
     * @param User $user Användare att sätta användarnamn för.
     * @param string $username Det nya användarnamnet.
     * @return bool Om ändringen lyckades.
     */
    function setUsername(User $user, string $username): bool {
        if ($this->getUserByName($username) != null) // En användare med namnet finns redan.
            return false;

        $user->name = trim($username);
        $this->userStore->update($user);
        return true;
    }

    /**
     * @param string $email Användarens e-postadress.
     * @return User|null Funnen användare eller null om den ej finns.
     */
    function getUserByEmail(string $email): ?User
    {
        return $this->userStore->getUserByEmail($email);
    }

    /**
     * @param string $name Användarens användarnamn.
     * @return User|null Funnen användare eller null om den ej finns.
     */
    function getUserByName(string $name): ?User
    {
        return $this->userStore->getUserByName($name);
    }

    /**
     * @param string $id Användarens ID.
     * @return User|null Funnen användare eller null om den ej finns.
     */
    function getUserById(string $id): ?User
    {
        return $this->userStore->getUserById($id);
    }

    /**
     * @return array Array med alla användare som finns registrerade.
     */
    function getUsers(): array
    {
        return $this->userStore->getAllUsers();
    }

    /**
     * Försöker ta bort en användare.
     * @param mixed $id Användarens ID.
     * @return bool Om användaren lyckades tas bort.
     */
    public function deleteUser(mixed $id): bool
    {
        $result = $this->userStore->deleteUserById($id);
        if($result){
            $this->sessionUpdateService->setUpdated($id);
        }

        return $result;
    }

    /**
     * Försöker uppdaterar en användare.
     * @param string $id Användarens ID.
     * @param Closure $callback Closure-funktion som tar den gamla användaren och förväntas returnera den uppdaterade.
     * @return bool Om användaren kunde hittas.
     */
    public function updateUser(string $id, Closure $callback): bool
    {
        $user = $this->userStore->getUserById($id);
        if($user == null)
            return false;

        $updated = $callback($user);
        if($updated != null){
            $this->userStore->update($updated);
            $this->sessionUpdateService->setUpdated($id);
        }

        return true;
    }

}