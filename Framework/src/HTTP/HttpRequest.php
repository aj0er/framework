<?php

namespace Framework\HTTP;

/**
 * Information från en inkommande HTTP-begäran.
 */
class HttpRequest
{

    /**
     * @var array Den parsade routen.
     */
    public array $route;
    /**
     * @var string Den faktiska routen som kommit in.
     */
    public string $rawRoute;

    /**
     * @var string HTTP metod, t.ex. "GET".
     */
    public string $method;
    /**
     * @var array Key-value map med förfrågans headers.
     */
    public array $headers;
    /**
     * @var array Params som mappats från routen.
     */
    public array $params;
    /**
     * @var array Params som mappats från querystring.
     */
    public array $query;
    /**
     * @var string|null Den inkommande bodyn, om sådan finns.
     */
    public ?string $body;

    private array $meta = array();

    /**
     * @param array $route Den parsade routen.
     * @param string $rawRoute Den faktiska routen som kommit in.
     * @param string $method HTTP metod, t.ex. "GET".
     * @param array $headers Key-value map med förfrågans headers.
     * @param array $query Params som mappats från querystring.
     * @param string|null $body Den inkommande bodyn, om sådan finns.
     */
    public function __construct(array $route, string $rawRoute, string $method,
                                array $headers, array $query, ?string $body)
    {

        $this->route = $route;
        $this->rawRoute = $rawRoute;
        $this->method = $method;
        $this->headers = $headers;
        $this->query = $query;
        $this->body = $body;
    }

    /**
     * Hämtar metan från den angivna nyckeln.
     * @param string $key Nyckel att hämta från.
     * @return mixed Funnen data eller null.
     */
    function getMeta(string $key): mixed {
        return $this->meta[$key] ?? null;
    }

    /**
     * Sätter data som ska renderas med Twig.
     * @param string $key Nyckeln att sätta datan som.
     * @param mixed $data Data att sätta.
     */
    function setViewModel(string $key, mixed $data){
        $array = $this->getMeta("viewModel") ?? array();
        $array[$key] = $data;

        $this->setMeta("viewModel", $array);
    }

    /**
     * Sätter datan med den angivna nyckeln
     * @param string $key Nyckeln att sätta datan som.
     * @param mixed $data Datan att sätta.
     */
    function setMeta(string $key, mixed $data){
        $this->meta[$key] = $data;
    }

}