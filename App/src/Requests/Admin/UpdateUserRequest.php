<?php

namespace App\Requests\Admin;

use App\Entity\Role;
use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då en användare ska uppdateras.
 */
class UpdateUserRequest extends CustomRequest
{

    /**
     * @var Role Användarens nya roll.
     */
    public Role $role;
    /**
     * @var string Användarens nya namn.
     */
    public string $name;

    function getMappingType(): RequestMappingType
    {
        return RequestMappingType::JSON_BODY;
    }

    public function getValidationRules(): ?array
    {
        return ["role" => "required", "name" => "required"];
    }

}