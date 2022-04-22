<?php

namespace App;

use App\Entity\Role;
use App\Middlewares\OnboardingMiddleware;
use App\Middlewares\UserSessionHelperMiddleware;
use App\Services\SessionUpdateService;
use Framework\Application;
use Framework\Middleware\Default\StaticMiddleware;
use App\Middlewares\SessionAuthMiddleware;
use Framework\DI\DependencyInjector;
use Framework\HTTP\HttpRequest;
use Framework\Routing\RequestExecutor;
use Framework\Util\HtmlValidator;
use Framework\Validation\RequestValidator;
use Framework\Routing\Router;
use Framework\Mapping\ObjectMapper;
use Twig\Environment;
use Twig\Extension\EscaperExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Huvudsakliga klassen för applikationen där det mesta konfigureras och instansieras.
 */
class App implements Application
{

    private RequestExecutor $executor;
    private DependencyInjector $injector;

   function __construct()
    {
        $jsonMapper = new ObjectMapper();

        /* Dependency Injection */
        $this->injector = new DependencyInjector();
        $this->injector->addInstance($jsonMapper);
        $this->injector->addInstance(new StaticMiddleware("/static", "resources/static"));
        $this->injector->addInstance(new SessionUpdateService("resources/updated_users.json"));
        $this->injector->addInstance(new HtmlValidator([
            "h2",
            "h3",
            "h4",
            "ol",
            "ul",
            "li",
            "blockquote",
            "p",
            "strong",
            "i",
            "#text",
            "#cdata-section"
        ], true));

        /* Template Engine */
        $loader = new FilesystemLoader('resources/views');
        $twig = new Environment($loader, [
            'cache' => false,
        ]);

        // Inlägg för Twig som använder vår HtmlValidator för att validera varje meddelande innan det renderas för användare.
        // Detta är användbart om en ny regel läggs till i HtmlValidator och det finns sparade inlägg i databasen som den då inte skulle appliceras på.
        $twig->getExtension(EscaperExtension::class)->setEscaper('customEscaper', function ($a, $html){
            if(!$this->injector->getInstance(HtmlValidator::class)->validate($html)){
                return "<p>Ogiltigt meddelande!</p>";
            }

            return $html;
        });

        $validator = new RequestValidator();

        $router = new Router();
        $this->executor = new RequestExecutor($router, $this->injector, $twig, $jsonMapper,
            $validator, [StaticMiddleware::class]);

        /* Root-sidor */
        $router->get("/", [Controllers\IndexController::class, 'indexPage'], [UserSessionHelperMiddleware::class]);
        $router->get("/login", [Controllers\AuthController::class, 'loginPage']);
        $router->get("/register", [Controllers\AuthController::class, 'registerPage']);
        $router->get("/onboarding", [Controllers\AuthController::class, 'onboardingPage']);

        /* Autentisering */
        $auth = $router->createGroup("/auth");
        $auth->post("/login", [Controllers\AuthController::class, 'login']);
        $auth->post("/register", [Controllers\AuthController::class, 'register']);
        $auth->post("/logout", [Controllers\AuthController::class, 'logout']);
        $auth->post("/username", [Controllers\AuthController::class, 'setUsername'], [SessionAuthMiddleware::class]);

        /* Admin-sidor */
        $admin = $router->createGroup("/admin", [[SessionAuthMiddleware::class, Role::ADMIN], UserSessionHelperMiddleware::class]);
        $admin->get("/", [Controllers\AdminController::class, 'indexPage']);
        $admin->get("/boards", [Controllers\AdminController::class, 'boardListPage']);
        $admin->get("/users", [Controllers\AdminController::class, 'userListPage']);

        /* Admin API */
        $adminApi = $router->createGroup("/api", [[SessionAuthMiddleware::class, Role::ADMIN]]);
        // Tavlor
        $adminApi->post("/boards", [Controllers\AdminController::class, 'createBoard']);
        $adminApi->delete("/boards/{id}", [Controllers\AdminController::class, 'deleteBoard']);
        // Användare
        $adminApi->get("/users", [Controllers\AdminController::class, 'listUsers']);
        $adminApi->delete("/users/{id}", [Controllers\AdminController::class, 'deleteUser']);
        $adminApi->put("/users/{id}", [Controllers\AdminController::class, 'updateUser']);
        // Trådar
        $adminApi->delete("/threads/{id}", [Controllers\AdminController::class, 'deleteThread']);

        /* Tavlor */
        $boards = $router->createGroup("/boards", [OnboardingMiddleware::class, UserSessionHelperMiddleware::class]);
        $boards->get("/", [Controllers\BoardController::class, 'listBoards']);
        $boards->get("/{id}", [Controllers\ThreadController::class, 'getThreadsByBoard']);
        $boards->get("/{id}/createThread", [Controllers\ThreadController::class, 'createThreadPage'], [SessionAuthMiddleware::class]);
        $boards->post("/{id}/threads", [Controllers\ThreadController::class, 'createThread'], [SessionAuthMiddleware::class]);

        /* Trådar */
        $threads = $router->createGroup("/threads", [OnboardingMiddleware::class, UserSessionHelperMiddleware::class]);
        $threads->get("/{id}", [Controllers\PostController::class, 'postListPage']);
        $threads->post("/{id}/posts", [Controllers\PostController::class, 'createPost'], [SessionAuthMiddleware::class]);

        /* Inlägg API (ej admin) */
        $postApi = $router->createGroup("/api/posts", [SessionAuthMiddleware::class]);
        $postApi->delete("/{id}", [Controllers\PostController::class, 'deletePost']);
        $postApi->put("/{id}", [Controllers\PostController::class, 'updatePost']);
    }

    function handle(HttpRequest $request)
    {
        $this->executor->handleRequest($request);
    }

}