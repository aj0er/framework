<?php

namespace App\Store;

use App\Entity\User;
use Framework\Mapping\ObjectMapper;
use Framework\Store\SQLiteStore;

class UserStore extends SQLiteStore
{

    private array $cache;

    public function __construct(ObjectMapper $mapper)
    {
        parent::__construct($mapper, "resources/database.sqlite", "users", User::class);

        parent::createTable([
            "id" => "binary(16)",
            "email" => "varchar(32)",
            "name" => "varchar(16)",
            "password" => "varchar(100)",
            "role" => "varchar(32)"
        ]);
    }

    public function create(User $user)
    {
        parent::insert($user);
    }

    public function update(User $user)
    {
        parent::executeNamed("UPDATE users SET id=:id, email=:email, name=:name, password=:password, role=:role WHERE id=:oid", [
            ":oid" => $user->id,
            ":id" => $user->id,
            ":email" => $user->email,
            ":name" => $user->name,
            ":password" => $user->password,
            ":role" => $user->role,
        ]);
    }

    public function getUserById(string $id): ?User
    {
        if(isset($this->cache)) {
            $user = $this->cache[$id] ?? null;
            if ($user != null) { // Om en cachad användare finns, returnera den.
                return $user;
            }
        }

        $user = parent::queryOne("SELECT * FROM users WHERE id=:id", [":id" => $id]);
        if (!isset($this->cache)) {
            $this->cache = array();
        }

        $this->cache[$id] = $user;
        return $user;
    }

    public function getUserByEmail(string $email): ?User
    {
        return parent::queryOne("SELECT * FROM users WHERE email=:email", [":email" => $email]);
    }

    public function getAllUsers(): array
    {
        return parent::queryNamed("SELECT * FROM users");
    }

    public function deleteUserById(mixed $id): int
    {
        $stmt = parent::execute("DELETE FROM users WHERE id=:id AND NOT role=:role", [":id" => $id, ":role" => "admin"]); // Admin-användare ska aldrig kunna tas bort via systemet.
        $res = $stmt->rowCount() > 0;
        if($res){   
            unset($this->cache[$id]); // Ta bort användaren från cachen.
        }

        return $res;
    }

    public function getUserByName(string $name): ?User
    {
        return parent::queryOne("SELECT * FROM users WHERE lower(name)=:name", [":name" => strtolower($name)]); // Namn är case insensitive, flera användare ska inte kunna ha samma namn med olika case.
    }

}