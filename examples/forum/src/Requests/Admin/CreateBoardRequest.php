<?php

namespace App\Requests\Admin;

use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då en tavla ska skapas.
 */
class CreateBoardRequest extends CustomRequest {

    /**
     * @var string Tavlans namn.
     */
    public string $name;

    function getMappingType(): RequestMappingType {
        return RequestMappingType::FORM;
    }

    function getValidationRules(): ?array {
        return [
            "name" => "required|max_length 16"
        ];
    }

}