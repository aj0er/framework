<?php

namespace App\Requests\Auth;

use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då en användare försöker logga in med användarnamn och lösenord.
 */
class LoginRequest extends CustomRequest
{

    public string $username;
    public string $password;

    public function getMappingType(): RequestMappingType
    {
        return RequestMappingType::FORM;
    }

    public function getValidationRules(): ?array
    {
        return [
            "username" => "required",
            "password" => "required"
        ];
    }

}