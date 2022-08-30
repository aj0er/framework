<?php

namespace App\Middlewares;

use App\Services\SessionUpdateService;
use Closure;
use Framework\Http\HttpRequest;
use Framework\Middleware\Middleware;

use App\Services\UserService;

/**
 * Middleware som dels används för att sätta en Twig-variabel samt kolla om användarens session är
 * giltig och alternativt ta bort/ändra den om en uppdatering skett i användarens data.
 */
class UserSessionHelperMiddleware extends Middleware
{

    private SessionUpdateService $sessionUpdateService;
    private UserService $userService;

    public function __construct(SessionUpdateService $sessionUpdateService, UserService $userService)
    {
        $this->sessionUpdateService = $sessionUpdateService;
        $this->userService = $userService;
    }

    public function handle(HttpRequest $request, Closure $next, ?array $args): mixed
    {

        $user = $_SESSION["user"] ?? null;

        if($user != null && $this->sessionUpdateService->isUpdated($user->id)){ // Om användaren är inloggad och en uppdatering skett
            $this->sessionUpdateService->clearUpdated($user->id);
            $user = $this->userService->getUserById($user->id);
            if($user == null){ // Om getUserById returnerar null är användaren borttagen, logga ut användaren.
                session_destroy();
                return parent::redirect("/login?status=2");
            }

            // Annars har någon slags uppdatering skett, uppdatera sessionen med den nya datan.
            $_SESSION["user"] = $user;
        }

        $request->setViewModel("user", $user);
        return $next($request);
    }

}