<?php

namespace Framework;

use Framework\HTTP\HttpRequest;

/**
 * En applikation i systemet som hanterar HTTP-förfrågningar.
 */
interface Application {

    /**
     * Hanterar en inkommande HTTP-förfrågan.
     * @param HttpRequest $request Förfrågan att hantera.
     */
    function handle(HttpRequest $request);

}