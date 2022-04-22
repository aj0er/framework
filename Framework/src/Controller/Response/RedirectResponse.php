<?php

namespace Framework\Controller\Response;

/**
 * Resultat som ska omdirigera användaren till en ny sida.
 */
class RedirectResponse
{

    /**
     * @var string URL som användaren ska omdirigeras till.
     */
    public string $url;

    /**
     * @param string $url URL som användaren ska omdirigeras till.
     */
    function __construct(string $url)
    {
        $this->url = $url;
    }

}