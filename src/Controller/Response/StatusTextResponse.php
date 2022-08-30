<?php

namespace Framework\Controller\Response;

/**
 * Resultat som ska visa en statuskod och ett meddelande.
 */
class StatusTextResponse
{

    /**
     * HTTP status-kod att returnera.
     */
    public int $code;
    /**
     * Sträng att returnera.
     */
    public string $text;

    /**
     * @param int $code HTTP status-kod att returnera.
     * @param string $text Sträng att returnera.
     */
    function __construct(int $code, string $text){
        $this->code = $code;
        $this->text = $text;
    }

}