<?php

namespace Framework\Routing;

use Framework\HTTP\HttpRequest;

/**
 * Router som registrerar och söker upp {@link Route} för inkommande förfrågningar.
 */
class Router
{

    /**
     * @var array|string[] Array med de delar som routen ska hantera, t.ex. "api"
     */
    private array $name;
    /**
     * @var array Array med middlewares som routern har.
     */
    public array $middlewares;

    /**
     * @var array Array med subrouters/grupper som routern har.
     */
    private array $groups;
    /**
     * @var array Array med direkta routes som routern har.
     */
    private array $routes;
    
    private const PATH_VAR_REGEX = '/{(.*?)}/';

    private array $groupVariables;

    function __construct(array $middlewares = [], string $path = "")
    {
        $this->middlewares = $middlewares;
        $this->groups = array();
        $this->routes = array();
        $this->name = $this->getRouteParts($path);
        for ($i = 0; $i < count($this->name); ++$i) {
            if (preg_match(self::PATH_VAR_REGEX, $this->name[$i])) {
                error_log("Path variables are currently only supported on individual routes, not groups/routers!");
            }
        }
    }

    /**
     * Skapar en ny subrouter/grupp som hanterar förfrågningar under denna router.
     * @param string $path Sökväg som den nya gruppen ska hantera. 
     * @param array $middlewares Array med middlewares som gruppen ska ha.
     * @return Router Subroutern som skapades.
     */
    public function createGroup(string $path, array $middlewares = []): Router
    {
        $group = new Router($middlewares, $path);
        array_push($this->groups, $group);
        return $group;
    }

    /**
     * Använder {@link route} för att skapa en ny route med GET som metod.
     */
    public function get(string $uri, array $handler, ?array $middlewares = null)
    {
        $this->route("GET", $uri, $handler, $middlewares);
    }

    /**
     * Använder {@link route} för att skapa en ny route med POST som metod.
     */
    public function post(string $uri, array $handler, ?array $middlewares = null)
    {
        $this->route("POST", $uri, $handler, $middlewares);
    }

    /**
     * Använder {@link route} för att skapa en ny route med PUT som metod.
     */
    public function put(string $uri, array $handler, ?array $middlewares = null)
    {
        $this->route("PUT", $uri, $handler, $middlewares);
    }

    /**
     * Använder {@link route} för att skapa en ny route med DELETE som metod.
     */
    public function delete(string $uri, array $handler, ?array $middlewares = null)
    {
        $this->route("DELETE", $uri, $handler, $middlewares);
    }

    /**
     * Skapar en ny route till denna routern.
     *
     * @param string $method HTTP-metod som denna route har.
     * @param string $uri Routens sökväg, / inkluderade.
     * @param array $handler Array med controllerklassens namn i första index och metodens namn i andra.
     * @param array|null $middlewares Array med middlewares som denna route ska ha, om den ska ha sådana.
     */
    public function route(string $method, string $uri, array $handler, ?array $middlewares = null)
    {
        $route = $this->getRouteParts($uri);
        $pathVariables = array();

        for ($i = 0; $i < count($route); ++$i) {
            $part = $route[$i];

            if (preg_match(self::PATH_VAR_REGEX, $part, $matches)) {
                $pathVariables[$i] = $matches[1]; // Index 1 är datan innanför {}
            }
        }

        array_push($this->routes, new Route($method, $route, $pathVariables, $handler, $middlewares));
    }

    /**
     * Kollar om en viss route matchar den inkommande.
     *
     * @param HttpRequest $request Inkommande HTTP-förfrågan.
     * @param Route $route Route som ska kollas.
     * @param int $depth Routerns djup, hur djupt vi gått i subgrupper.
     * @return array|null Inkommande path variabler eller null om routen inte matchar.
     */
    private function isMatchingRoute(HttpRequest $request, Route $route, int $depth): ?array
    {
        $parts = $route->parts;
        $offset = count($this->name) + $depth; // Offset för router-grupper, t.ex. / under /api/users har offset 2

        // Om antalet route delar inte matchar är den inte giltig.
        if(count($request->route) - $offset != count($parts))
            return null;

        $incomingPathVars = [];
        for ($i = 0; $i < count($parts) || $i == 0; ++$i) { // Iterera över routens delar, även den första 
            $idx = $i + $offset;
            if ($idx >= count($request->route)){
                return null;
            }

            $data = $request->route[$i + $offset]; // Värdet i den inkommande förfrågan

            if (array_key_exists($i, $route->variables)) { // Nuvarande del är en "param" variabel i routen och kan ha vilket värde som helst
                if ($data == "") {
                    return null;
                }

                $key = $route->variables[$i]; // Paramvariabelns namn, t.ex. /{id} => "id"
                $incomingPathVars[$key] = $data;
            } else {
                $part = $parts[$i];
                if ($part != $data) { // Nuvarande del måste ha samma värde som den i routen, annars matchar de inte
                    return null;
                }
            }
        }

        return $incomingPathVars;
    }

    /**
     * Appendar middlewares på denna routern med middlewares i en array.
     * @param ?array $middlewares Yttre middlewares som ska mergas.
     * @return array Array med appendade middlewares.
     */
    private function appendMiddlewares(?array $middlewares = null): array
    {
        return $middlewares != null 
            ? array_merge($this->middlewares, $middlewares) 
            : $this->middlewares;
    }

    /**
     * Försöker hitta en matchande route för den inkommande förfrågan.
     * @param HttpRequest $request Inkommande förfrågan
     * @param ?array $middlewares Array med middlewares som fanns på parent routern.
     * @param int $depth Routerns djup, hur djupt vi gått i subgrupper. Ska vara -1 vid root-routern /
     * @return RoutingResult|null Funnen route eller null om ingen route som matchar hittades.
     */
    function findMatchingRoute(HttpRequest $request, ?array $middlewares = null, int $depth = -1): ?RoutingResult
    {
        $nameLen = count($this->name);
        for ($i = 0; $i < $nameLen; $i++) {
            if(($i + $depth) >= count($request->route)){
                return null;
            }

            $incPath = $request->route[$i + $depth];
            // Kollar så att gruppen matchar, om gruppen har sökvägen /api/users måste även den inkommande förfrågan börja med /api/users
            if ($incPath != $this->name[$i]) {
                return null;
            }
        }

        foreach ($this->routes as $route) {
            if ($route->method != $request->method) { // Routen måste ha samma metod som den inkommande förfrågan.
                continue;
            }

            if (count($route->parts) == 0) { // Routen är en / route, t.ex. / direkt eller / under gruppen /boards                
                $incPartCount = count($request->route);
                $varOffset = count($this->name) + $depth; // Hur mycket offset som gruppen har, vid /api/users/test måste vi börja läsa vid $varOffset då routes inte innehåller gruppens namn.

                $isRootRoot = $incPartCount == 0 && $varOffset == -1; // Om routen är "ultra" root, alltså / utan några grupper.
                $isGroupRoot = $incPartCount == $varOffset;

                if ($isRootRoot || $isGroupRoot) {
                    return new RoutingResult($route, $this->appendMiddlewares($middlewares));
                }
            } else {
                $result = $this->isMatchingRoute($request, $route, $depth == -1 ? 0 : $depth); // Om depth är -1 är det "root" gruppen / och det finns därför ingen depth.
                if ($result !== null) {
                    $request->params = $result; // Result returnerar inkommande path variabler.
                    return new RoutingResult($route, $this->appendMiddlewares($middlewares));
                }
            }
        }

        foreach ($this->groups as $group) { // Kolla i routerns undergrupper om routen matchar i någon av dem.
            $result = $group->findMatchingRoute($request, $this->middlewares, $depth + 1);
            if ($result != null)
                return $result;
        }

        return null;
    }

    /**
     * Hjälpmetod för att dela upp en sökvägssträng till array med delar.
     * @param string $uri Sökväg att dela upp.
     * @return array Array med routens delar.
     */
    public static function getRouteParts(string $uri): array
    {
        $route = explode("/", $uri);
        if (count($route) == 2 && $route[0] === "" && $route[1] === "") { // Routen är en / route, då bör arrayen vara helt tom istället för att innehålla tomma strängar.
            $route = array();
        } else {
            $route = array_slice($route, 1);
        }

        return $route;
    }

}