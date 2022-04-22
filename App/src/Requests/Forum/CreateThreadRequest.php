<?php

namespace App\Requests\Forum;

use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;

/**
 * Förfrågan då användare skapar en ny tråd.
 */
class CreateThreadRequest extends CustomRequest
{

    /**
     * @var string Trådens titel.
     */
    public string $title;
    /**
     * @var string HTML-innehållet för det tillhörande första inlägget.
     */
    public string $content;

    function getMappingType(): RequestMappingType
    {
        return RequestMappingType::FORM;
    }

    public function getValidationRules(): ?array
    {
        return [
            "title" => "required",
            "content" => "required"
        ];
    }

}