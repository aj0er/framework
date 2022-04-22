<?php

namespace Framework\Middleware;

use Closure;
use Framework\Controller\Response\Responses;
use Framework\Http\HttpRequest;

/**
 * Ett middleware som hanterar en begäran innan den når en controller.
 * Kan stoppa pipelinen och returnera något i förtid.
 */
abstract class Middleware extends Responses
{

    /**
     * Hanterar en inkommande HTTP-begäran.
     * Den förväntas att returnera antingen "$next($request)" för att gå vidare till nästa middleware eller
     * något annat om den ska sluta i pipelinen, för att t.ex. returnera 403 forbidden.
     *
     * @param HttpRequest $request Inkommande begäran.
     * @param Closure $next Funktion som dirigerar begäran till nästa middleware.
     * @return mixed "$next($request)" för att gå till nästa eller något annat för att stoppa pipelinen.
     */
    public abstract function handle(HttpRequest $request, Closure $next, ?array $args): mixed;

}