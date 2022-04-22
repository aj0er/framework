<?php

namespace App\Requests\Auth;

use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då användare försöker registrera sig.
 */
class RegisterUserRequest extends CustomRequest {

    public string $username;
    public string $password;

    /**
     * @var string Verifiering att lösenordet skrivits in rätt.
     */
    public string $confirm;

    function getMappingType(): RequestMappingType {
        return RequestMappingType::FORM;
    }

    function getValidationRules(): ?array {
        return [
            "username" => "required|email",
            "password" => "required|min_length 8",
            "confirm" => "required"
        ];
    }

}