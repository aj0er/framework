<?php

namespace App\Middlewares;

use Closure;
use Framework\Http\HttpRequest;
use Framework\Middleware\Middleware;
use Framework\Util\StatusCode;

/**
 * Middleware som ser till att användaren alltid är inloggad. Kan även skapas med frivillig parameter för role,
 * då måste användaren även ha en viss roll för att kunna visa sidan.
 */
class SessionAuthMiddleware extends Middleware
{

    public function handle(HttpRequest $request, Closure $next, ?array $args): mixed
    {
        $user = $_SESSION["user"] ?? null;
        if ($user == null)
            return parent::redirect("/login");

        $role = $args[0] ?? null;
        if ($role != null && $user->role != $role) {
            // Middlewaret har role argumentet, då måste användarens roll vara exakt den rollen.
            // I framtiden kanske det är bättre med en hierarki där t.ex även SUPER_ADMIN kan visa sidan.
            return parent::status(StatusCode::Forbidden);
        }

        return $next($request);
    }

}