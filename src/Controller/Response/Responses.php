<?php

namespace Framework\Controller\Response;

use Framework\Util\StatusCode;

/**
 * Klass med vanligen använda responser.
 */
class Responses
{

    /**
     * Returnerar en Twig-vy som renderas.
     * @param string $name Namn på vyn.
     * @param array $model Variabler som ska renderas i vyn.
     * @return ViewResponse
     */
    function view(string $name, array $model = []): ViewResponse
    {
        return new ViewResponse($name, $model);
    }

    /**
     * Returnerar en HTTP status-kod.
     * @param int $code HTTP status-kod.
     * @return int
     */
    function statusRaw(int $code): int
    {
        return $code;
    }

    /**
     * Returnerar en HTTP status-kod.
     * @param StatusCode $code Enum värde för en vanlig status-kod.
     * @return int
     */
    function status(StatusCode $code): int {
        return $this->statusRaw($code->value);
    }

    /**
     * Returnerar en sträng tillsammans med en HTTP-statuskod.
     * @param int $code HTTP status-kod.
     * @param string $text
     */
    function statusText(int $code, string $text): StatusTextResponse {
        return new StatusTextResponse($code, $text);
    }

    /**
     * Returnerar och visar/laddar ned en fil.
     * @param string $fileName Filens sökväg.
     * @return FileResponse
     */
    function file(string $fileName): FileResponse {
        return new FileResponse($fileName);
    }

    /**
     * Returnerar en omdirigering till webbläsaren.
     * @param string $url URL att omdirigera till.
     * @return RedirectResponse
     */
    function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

}