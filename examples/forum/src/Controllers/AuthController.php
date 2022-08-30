<?php

namespace App\Controllers;

use App\Requests\Auth\LoginRequest;
use App\Requests\Auth\RegisterUserRequest;
use App\Requests\Auth\SetUsernameRequest;
use App\Services\UserService;
use Framework\Controller\Controller;
use Framework\Controller\Response\RedirectResponse;
use Framework\Controller\Response\StatusTextResponse;
use Framework\Controller\Response\ViewResponse;
use Framework\Util\StatusCode;

/**
 * Controller för allt gällande autentisering, sidor som API:er.
 */
class AuthController extends Controller
{

    private UserService $userService;

    function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    function loginPage(): ViewResponse
    {
        return parent::view("login");
    }

    function registerPage(): ViewResponse
    {
        return parent::view("register");
    }

    function onboardingPage(): ViewResponse|RedirectResponse
    {
        $user = $_SESSION["user"];
        if($user == null || $user->name != null)
            return parent::redirect("/");

        return parent::view("onboarding");
    }

    function login(LoginRequest $request): int|RedirectResponse
    {
        if($request->validate() != null)
            return parent::status(StatusCode::BadRequest);

        $user = $this->userService->authenticate(strtolower($request->username), $request->password);
        if ($user == null)
            return parent::redirect("/login?status=0");

        $_SESSION["user"] = $user;
        return parent::redirect("/boards");
    }

    function register(RegisterUserRequest $request): RedirectResponse|StatusTextResponse
    {
        $error = $request->validate();
        if($error != null){
            return parent::redirect("/register?status=2");
        }

        $username = $request->username;
        $password = $request->password;
        $confirm  = $request->confirm;

        if($confirm != $password){
            return parent::redirect("/register?status=0");
        }

        if($this->userService->createUser(strtolower($username), $password) == null){
            return parent::redirect("/register?status=1");
        }

        return parent::redirect("/login");
    }

    function logout(): RedirectResponse
    {
        session_destroy();
        return parent::redirect("/login?status=1");
    }

    function setUsername(SetUsernameRequest $request): StatusTextResponse|RedirectResponse
    {
        $user = $_SESSION["user"];

        if($user->name != null)
            return parent::statusText(400, "Du kan inte ändra ditt användarnamn!");

        if($request->validate() != null)
            return parent::redirect("/onboarding?status=1");

        if($this->userService->setUsername($user, $request->username)){
            return parent::redirect("/boards");
        } else {
            return parent::redirect("/onboarding?status=0");
        }
    }

}