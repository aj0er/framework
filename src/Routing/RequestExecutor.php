<?php

namespace Framework\Routing;

use Framework\Controller\Response\FileResponse;
use Framework\Controller\Response\StatusTextResponse;
use Framework\Controller\Response\ViewResponse;
use Framework\DI\DependencyInjector;
use Framework\Controller\Response\RedirectResponse;
use Framework\HTTP\HttpRequest;
use Framework\Mapping\CustomRequest;
use Framework\Mapping\RequestMappingType;
use Framework\Util\ConsoleColors;
use Framework\Validation\RequestValidator;
use Framework\Store\CustomDataObject;
use Framework\Mapping\ObjectMapper;
use Mimey\MimeTypes;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Klass som hanterar mycket av processerna för inkommande HTTP-förfrågningar, från att kolla upp routen
 * med Router till att ett resultat kommer ut på andra sidan.
 */
class RequestExecutor
{

    private Router $router;
    private DependencyInjector $injector;
    private Environment $renderer;
    private MimeTypes $mimeTypes;
    private RequestValidator $validator;
    private array $middlewares;
    private ObjectMapper $mapper;

    function __construct(Router             $router,
                         DependencyInjector $injector,
                         Environment        $renderer,
                         ObjectMapper       $mapper,
                         RequestValidator   $validator,
                         array              $middlewares = array())
    {
        $this->router = $router;
        $this->injector = $injector;
        $this->renderer = $renderer;
        $this->validator = $validator;
        $this->mapper = $mapper;
        $this->middlewares = $middlewares;
        $this->mimeTypes = new MimeTypes;
    }

    private function respondView(ViewResponse $response, HttpRequest $request)
    {
        $name = $response->name;
        $model = array_merge($request->getMeta("viewModel") ?? array(), $response->model);

        try {
            echo $this->renderer->render($name . '.html', $model);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            echo "Unable to load this page, please contact the site administrator!";
            error_log($e);
            return;
        }
    }

    private function respondRedirect(RedirectResponse $response)
    {
        header("Location: " . $response->url);
    }

    private function respondFile(FileResponse $response)
    {
        $fileName = $response->filePath;
        $data = file_get_contents($fileName);

        if ($data === false) { // Filen finns inte.
            http_response_code(404);
            return;
        }

        $mimeType = $this->mimeTypes->getMimeType(pathinfo($fileName)["extension"]);
        $this->respondContentType($mimeType);

        echo $data;
    }

    private function respondStatusCode(int $statusCode)
    {
        http_response_code($statusCode);
    }

    private function respondStatusText(StatusTextResponse $response)
    {
        $this->respondStatusCode($response->code);
        $this->respondText($response->text);
    }

    private function respondText(string $text)
    {
        $this->respondContentType("text/plain");
        echo $text;
    }

    private function respondContentType(string $contentType){
        header("Content-Type: " . $contentType);
    }

    private function respondJSON(mixed $response)
    {
        $this->respondContentType("application/json");
        if (is_array($response)) { // Om det är en array där vissa element är av typen CustomJSONSerializable
            $serializeFunction = function ($value) {
                if ($value instanceof CustomDataObject) {
                    return $value->getData(); // Mappa det serialiserade värdet istället
                } else {
                    return $value;
                }
            };

            $response = array_map($serializeFunction, $response);
        } else if ($response instanceof CustomDataObject) { // Om värdet är av typen CustomJSONSerializable
            echo json_encode($response->getData());
            return;
        }

        echo json_encode($response);
    }

    /**
     * Hanterar en inkommande HTTP-förfrågan, letar upp routen, exekverar den (tillsammans med middlewares)
     * och skickar ut ett lämpligt resultat till klienten.
     *
     * @param HttpRequest $request HTTP-förfrågan att hantera
     */
    public function handleRequest(HttpRequest $request)
    {
        if (!isset($_SESSION))
            session_start(); // Starta sessionen om den inte finns

        $result = $this->router->findMatchingRoute($request) ?? null;

        $response = $this->executeRequest($result, $request);
        $this->handleResponse($response, $request);

        error_log($this->generateRequestLogMessage($request, $result));
    }

    private function handleResponse(mixed $response, HttpRequest $request)
    {
        if ($response != null) {
            if (is_string($response)) { // Strängar
                $this->respondText($response);
            } else if (is_int($response)) { // HTTP status-kod
                $this->respondStatusCode($response);
            } else if ($response instanceof ViewResponse) { // Twig vy
                $this->respondView($response, $request);
            } else if ($response instanceof RedirectResponse) { // Redirect
                $this->respondRedirect($response);
            } else if ($response instanceof FileResponse) { // Filer
                $this->respondFile($response);
            } else if ($response instanceof StatusTextResponse) { // Text och status-kod
                $this->respondStatusText($response);
            } else { // Allt annat, json
                $this->respondJSON($response);
            }
        } else {
            http_response_code(404);
        }
    }

    /**
     * Genererar ett färgat loggmeddelande att skicka i konsolen då en förfrågan hanterats.
     * @param HttpRequest $request Förfrågan som detta gäller
     * @return string Det resulterade loggmeddelandet.
     */
    private function generateRequestLogMessage(HttpRequest $request, ?RoutingResult $result): string
    {
        $code = http_response_code();
        if ($code >= 200 && $code < 300) { // OK
            $color = ConsoleColors::GREEN->value;
        } else if ($code >= 400 && $code < 600) { // Error
            $color = ConsoleColors::RED->value;
        } else { // Unknown
            $color = ConsoleColors::YELLOW->value;
        }

        $controllerText = "";
        if ($result != null) {
            $className = $result->route->handler[0];
            $methodName = $result->route->handler[1];

            $controllerText = strtr(" {yellow}({className}#{methodName}){reset}", [
                "{yellow}" => ConsoleColors::YELLOW->value,
                "{className}" => $className,
                "{methodName}" => $methodName,
                "{reset}" => ConsoleColors::RESET->value
            ]);
        }

        return strtr("◆ {blue}{method} {yellow}{route}{yellow}{controller}{codeColor} [{code}] {blue}({timing}){reset}", [
            "{blue}" => ConsoleColors::BLUE->value,
            "{method}" => $request->method,
            "{yellow}" => ConsoleColors::YELLOW->value,
            "{route}" => $request->rawRoute,
            "{codeColor}" => $color,
            "{code}" => $code,
            "{reset}" => ConsoleColors::RESET->value,
            "{controller}" => $controllerText,
            "{blue}" => ConsoleColors::BLUE->value,
            "{timing}" => "20ms"
        ]);
    }

    /**
     * Exekverar en HTTP-förfrågan med potentiell route, exekverar alla middlewares och till sist controllern om sådan finns.
     * @param RoutingResult|null $result Resultatet från routern.
     * @param HttpRequest $request HTTP-förfrågan som kom in.
     * @return mixed
     */
    private function executeRequest(?RoutingResult $result, HttpRequest $request): mixed
    {
        $handler = null;
        $controller = null;

        if ($result != null) { // Om en controller fanns för denna routen
            $handler = $result->route->handler;
            $controller = $this->injector->getInstance($handler[0]);
        }

        $middlewares = array_merge(
                $this->middlewares, // Först globala middlewares
                $result->routerMiddlewares ?? array(), // Sedan middlewares i gruppen
                $result->route->middlewares ?? array()) ?? array(); // Sist, middlewares på själva routen

        $i = 0;
        $pipelineExecutorFunc = function (HttpRequest $request) use (&$i, $controller, $handler, $middlewares, &$pipelineExecutorFunc) {
            if ($i >= count($middlewares)) { // Om vi nått slutet av pipelinen, försök köra controllern.
                if ($handler != null) {
                    return $this->executeController($request, $controller, $handler[1]);
                } else {
                    return null; // Det finns inget middleware som stoppar requesten eller en controller som kan hantera den, returnera 404
                }
            }

            $middlewareData = $middlewares[$i];
            $middlewareArgs = null;

            if(is_array($middlewareData)){
                $middlewareClass = $middlewareData[0];
                $middlewareArgs = array_splice($middlewareData, 1);
            } else {
                $middlewareClass = $middlewareData;
            }

            $middleware = $this->injector->getInstance($middlewareClass); // Hämta instansen av middlewaren
            $i++; // Gå till nästa middleware i pipelinen

            // Middlewaren får denna anonyma funktionen inskickad som den kan anropa för att advancera i pipelinen.
            // T.ex. $next($request) anropar denna funktionen igen, variabeln $i håller reda på index i pipelinen
            return $middleware->handle($request, $pipelineExecutorFunc, $middlewareArgs);
        };

        return $pipelineExecutorFunc($request);
    }

    /**
     * Försöker att exekverar en controller med data från en HTTP-förfrågan.
     * @param HttpRequest $request Inkommande HTTP-förfrågan.
     * @param object $controller Instansen av controllern.
     * @param string $methodName Metoden i controllerklassen att exekvera.
     */
    private function executeController(HttpRequest $request, object $controller, string $methodName)
    {
        try {
            $reflectionClass = new ReflectionClass($controller);
            $method = $reflectionClass->getMethod($methodName);
            $parameters = $method->getParameters();

            $paramInstances = array();
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type == null) {
                    return null; // Parametern har ingen typ och vi vet därför inte vad vi behöver injecta den med.
                }

                $value = $this->constructControllerParam($type, $request);
                if ($value == null) {
                    error_log("Unknown type " . $type->getName() . " requested by controller!");
                    return 400;
                } else {
                    array_push($paramInstances, $value);
                }
            }

            return $method->invoke($controller, ...$paramInstances);
        } catch (ReflectionException $ex) {
            error_log($ex->getMessage());
        }

        return null;
    }

    /**
     * Konstruerar en parameter i en controllerfunktion genom att kolla upp dess typ
     * och försöker skapa ett lämpligt objekt att fylla den med.
     *
     * @param ReflectionNamedType $type Parameterns typ.
     * @param HttpRequest $request HTTP-förfrågan som innehåller datan.
     * @return mixed Ny instans av parameterns objekt eller null om ett fel uppstod.
     */
    private function constructControllerParam(ReflectionNamedType $type, HttpRequest $request): mixed
    {
        if (is_subclass_of($type->getName(), CustomRequest::class)) {
            try {
                $requestClass = new ReflectionClass($type->getName());
                $param = $requestClass->newInstance();

                $param->validator = $this->validator;
                $param->request = $request;

                $mappingType = $param->{"getMappingType"}();

                $mappingObject = null;
                switch ($mappingType) {
                    case RequestMappingType::FORM:
                    {
                        $mappingObject = $request->query;
                        break;
                    }
                    case RequestMappingType::JSON_BODY:
                    {
                        $mappingObject = json_decode($request->body, true);
                        break;
                    }
                }

                $param->data = (array)$mappingObject;
                if (!$this->mapper->mapObjectProperties($mappingObject, $param)) {
                    echo "Something went wrong mapping data to custom request object!";
                    return null;
                }

                return $param;
            } catch (ReflectionException $ex) {
                error_log($ex);
                return null;
            }
        } else if ($type->getName() === HttpRequest::class) {
            return $request;
        } else {
            return null;
        }
    }

}