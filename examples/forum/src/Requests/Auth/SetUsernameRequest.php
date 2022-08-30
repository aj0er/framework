<?php

namespace App\Requests\Auth;

use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då användare ställer in sitt användarnamn efter att de skapat ett konto.
 */
class SetUsernameRequest extends CustomRequest
{

    /**
     * @var string Användarens nya användarnamn.
     */
    public string $username;

    public function getMappingType(): RequestMappingType
    {
        return RequestMappingType::FORM;
    }

    public function getValidationRules(): ?array
    {
        return [
            "username" => "required|min_length 2|max_length 20"
        ];
    }

}