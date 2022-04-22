<?php

namespace App\Middlewares;

use Closure;
use Framework\Http\HttpRequest;
use Framework\Middleware\Middleware;

/**
 * Middleware som redirectar användare till /onboarding om de är nyregistrerade och inte valt ett namn.
 */
class OnboardingMiddleware extends Middleware
{

    public function handle(HttpRequest $request, Closure $next, ?array $args): mixed
    {
        $user = $_SESSION["user"] ?? null;
        if($user == null || $user->name != null)
            return $next($request);

        return parent::redirect("/onboarding");
    }

}