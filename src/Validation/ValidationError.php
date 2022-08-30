<?php

namespace Framework\Validation;

/**
 * En error från {@link RequestValidator} då ett fel i valideringen skett.
 */
class ValidationError
{

    /**
     * @var string Fältet som misslyckats i en förfrågan.
     */
    public string $field;
    /**
     * @var string Fulla regelsträngen som testades.
     */
    public string $fullRule;
    /**
     * @var string Den enskilda regeln som misslyckades.
     */
    public string $rule;
    /**
     * @var mixed Det inkommande värdet som var ogiltigt.
     */
    public mixed $incomingValue;

    public function __construct(string $field, string $fullRule, string $rule, mixed $incomingValue)
    {
        $this->field = $field;
        $this->fullRule = $fullRule;
        $this->rule = $rule;
        $this->incomingValue = $incomingValue;
    }

    /**
     * @return string Meddelande med lättare förstådd information om vad som gick fel i valideringen.
     */
    function getMessage(): string {
        return "Validation failed at field \"" . $this->field . "\", rule: \"" . $this->rule . "\"";
    }

}