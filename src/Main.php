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
     * @var string|null Subpath som används om sidan ska serveras från en undersida, t.ex. example.com/app/
     */
    private ?string $subPath;

    /**
     * @param Application $app Applikationen som servern ska köra.
     */
    function __construct(Application $app, ?string $subPath = null){
        $this->app = $app;
        $this->subPath = $subPath;
    }

    /**
     * Hanterar en inkommande HTTP-förfrågan och konstruerar en {@link HttpRequest} från den.
     */
    function handle(){
        $timingStart = hrtime(true);
        $uri = $_SERVER["REQUEST_URI"]; // Den faktiskt inkommande URL-en

        if($this->subPath != null){
            $pos = strpos($uri, $this->subPath);
            if ($pos !== false) {
                $uri = substr_replace($uri, "", $pos, strlen($this->subPath));
            }
        }

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

        $request = new HttpRequest($requestedRoute, $uri, $method, getallheaders(), $queryParams, $body, $timingStart);
        $this->app->handle($request);
    }
    
}