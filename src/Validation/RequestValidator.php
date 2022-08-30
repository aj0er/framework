<?php

namespace Framework\Validation;

use Closure;
use Framework\Mapping\CustomRequest;

/**
 * Klass som validerar inkommande HTTP-förfrågningar från regler funna i en {@link CustomRequest}.
 */
class RequestValidator {

    /**
     * @var array Array med alla validators. Key är validators namn.
     */
    private array $validators;

    function __construct(){
        $this->registerValidator("required",
            fn($val, $args) => $val != null);

        $this->registerValidator("email",
            fn($val, $args) => filter_var($val, FILTER_VALIDATE_EMAIL));

        $this->registerValidator("max_length", 
            fn($val, $args) => strlen($val) <= intval($args[0]));

        $this->registerValidator("min_length",
            fn($val, $args) => strlen($val) >= intval($args[0]));
    }

    /**
     * Registrerar en ny validator.
     * @param string $name validatorns namn.
     * @param Closure $validator validators funktion med signaturen (värde, args), där värdet är
     * det inkommande värdet och args är argument för validatorfunktionen, t.ex. 5 för "min_length 5".
     */
    function registerValidator(string $name, Closure $validator){
        $this->validators[$name] = $validator;
    }

    /**
     * Validerar en förfrågan.
     * @param CustomRequest $request Förfrågan att validera.
     * @return ValidationError|null Valideringserror eller null om allt gick fint.
     */
    function validateRequest(CustomRequest $request): ?ValidationError {
        $rules = $request->getValidationRules();
        if($rules == null)
            return null;

        foreach ($rules as $field => $rule) {
            $fieldValue = $request->data[$field] ?? null;
            $errorRule = $this->validateRule($fieldValue, $rule);

            if($errorRule != null){ // Det finns en error.
                return new ValidationError($field, $rule, $errorRule, $fieldValue);
            }
        }

        return null;
    }

    /**
     * Validerar en enskild validatorregel.
     * @param mixed $value Värdet som valideras.
     * @param string $ruleString Regelsträngen, t.ex. "min_length 5|max_length 15"
     * @return string|null Sträng med regeln som värdet misslyckades för eller null om inga fel hittades.
     */
    private function validateRule(mixed $value, string $ruleString): ?string {
        $rules = explode("|", $ruleString);

        foreach($rules as $rule){
            $data = explode(" ", $rule);
            $ruleName = $data[0];
            $validator = $this->validators[$ruleName] ?? null;

            if($validator == null || !$validator($value, array_slice($data, 1))){ // Om validatorn inte finns eller om den misslyckades.
                return $ruleName; // Returnera namnet på regeln som misslyckades.
            }
        }

        return null;
    }

}