<?php

namespace Framework;

use Framework\HTTP\HttpRequest;
use Framework\Routing\Router;

/**
 * Huvudsakliga ingångsklassen för förfrågningar till servern.
 */
class Main {

    /**
     * @var Application Applikationen som servern kör.
     */
    private Application $app;

    /**
     * @param Application $app Applikationen som servern ska köra.
     */
    function __construct(Application $app){
        $this->app = $app;
    }

    /**
     * Hanterar en inkommande HTTP-förfrågan och konstruerar en {@link HttpRequest} från den.
     */
    function handle(){
        $uri = $_SERVER["REQUEST_URI"]; // Den faktiskt inkommande URL-en

        $queryPos = strpos($uri, '?');
        $uri = substr($uri, 0, $queryPos != false ? $queryPos : strlen($uri)); // Strippa URI från querystringen
        
        $method = $_SERVER['REQUEST_METHOD']; // HTTP metod
        $requestedRoute = Router::getRouteParts($uri);
        $queryParams = $method == "POST" ? $_POST : $_GET; // Läs från variabeln för POST om förfrågan är har metoden POST

        $body = null;
        switch ($method) {
            case "POST":
            case "PUT":
                $body = file_get_contents('php://input'); // Vid POST och PUT finns body i php://input
        }

        $request = new HttpRequest($requestedRoute, $uri, $method, getallheaders(), $queryParams, $body);
        $this->app->handle($request);
    }
    
}