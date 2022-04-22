<?php

namespace Framework\Routing;

/**
 * Resultat returnerat från en {@link Router} då en route hittats.
 */
class RoutingResult
{

    /**
     * @var Route Funnen route.
     */
    public Route $route;
    /**
     * @var array Array med middlewares som fanns i routern som hittade routen.
     */
    public array $routerMiddlewares;

    public function __construct(Route $route, array $middlewares)
    {
        $this->route = $route;
        $this->routerMiddlewares = $middlewares;
    }

}