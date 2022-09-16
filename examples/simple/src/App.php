<?php

namespace Simple;

use Framework\Application;
use Framework\DI\DependencyInjector;
use Framework\HTTP\HttpRequest;
use Framework\Mapping\ObjectMapper;
use Framework\Middleware\Default\StaticMiddleware;
use Framework\Routing\RequestExecutor;
use Framework\Routing\Router;
use Framework\Validation\RequestValidator;

use Simple\Controllers\SimpleController;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class App implements Application {
    
    private RequestExecutor $executor;

    function __construct(){
        $loader = new FilesystemLoader('resources/views');
        $twig = new Environment($loader, [
            'cache' => false,
        ]);

        $di = new DependencyInjector();
        $di->addInstance(new StaticMiddleware("/static", "resources/static"));

        $router = new Router();
        $router->get("/", [SimpleController::class, 'index']);

        $this->executor = new RequestExecutor($router, $di,
            $twig, new ObjectMapper(), new RequestValidator(), [StaticMiddleware::class]);
    }

    function handle(HttpRequest $request)
    {
        $this->executor->handleRequest($request);
    }
    
}