<?php

namespace Framework\Mapping;

use Framework\HTTP\HttpRequest;
use Framework\Routing\RequestExecutor;
use Framework\Validation\RequestValidator;
use Framework\Validation\ValidationError;

/**
 * En skräddarsydd förfrågan i systemet som kan ha vissa valideringsregler, t.ex. "LoginRequest" med
 * "username" och "password" som properties.
 */
abstract class CustomRequest {

    /**
     * @var HttpRequest|null Den originella HTTP-förfrågan.
     */
    public ?HttpRequest $request;
    /**
     * @var RequestValidator|null Validatorn som validerar förfrågan.
     */
    public ?RequestValidator $validator;
    /**
     * @var array|null Mappad data från förfrågan, från ett formulär eller en JSON body.
     */
    public ?array $data;

    /**
     * Validerar förfrågan med validatorn som sätts från {@link RequestExecutor}
     * @return ValidationError|null Error från validationen eller null om förfrågan är felfri.
     */
    function validate(): ?ValidationError {
        if($this->validator == null)
            return null;

        return $this->validator->validateRequest($this);
    }

    /**
     * Får förfrågans mappningstyp.
     * @return RequestMappingType
     */
    abstract function getMappingType(): RequestMappingType;

    /**
     * Får förfrågans valideringsregler eller null om inga finns.
     * @return array|null
     */
    function getValidationRules(): ?array {
        return null;
    }

}