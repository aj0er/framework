<?php

namespace App\Entity;

use Framework\Store\CustomDataObject;

/**
 * En användare i systemet.
 */
class User extends CustomDataObject
{

    /**
     * @var string Användarens ID.
     */
    public string $id;
    /**
     * @var string Användarens e-postadress.
     */
    public string $email;
    /**
     * @var string|null Användarens namn om hen har satt ett sådant.
     */
    public ?string $name;
    /**
     * [JsonIgnore]
     * @var string Användarens hashade lösenord.
     */
    public string $password;
    /**
     * @var Role Användarens roll.
     */
    public Role $role;

    /**
     * @param string $id Användarens ID.
     * @param string $email Användarens e-postadress.
     * @param string|null $name Användarens namn om hen har satt ett sådant.
     * @param string $password Användarens hashade lösenord.
     * @param Role $role Användarens roll.
     */
    public function __construct(string $id, string $email, ?string $name, string $password, Role $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->role = $role;
    }

    /**
     * @return string Namnet (backed value) på användarens roll.
     */
    function getRoleName(): string {
        return $this->role->value;
    }

}