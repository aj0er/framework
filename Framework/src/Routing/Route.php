<?php

namespace Framework\Routing;

/**
 * Innehåller info om en route som registreras i en router.
 */
class Route
{

    /**
     * @var string HTTP-metod som denna route ska hantera.
     */
    public string $method;
    /**
     * @var array Array med de olika delarna av routen.
     */
    public array $parts;
    /**
     * @var array Array med de dynamiska variablerna i sökvägen, t.ex. /users/{id} där "id" är en variabel.
     */
    public array $variables;
    /**
     * @var array Array innehållande controllerns klass och namnet på metoden som ska köras, t.ex. [UserController::class, 'listUsers']
     */
    public array $handler;
    /**
     * @var array|null Optional array med middlewares som denna routen ska utföra innan controllern ska anropas.
     */
    public ?array $middlewares;

    function __construct(string $method, array $parts,
                         array  $variables, array $handler, ?array $middlewares)
    {

        $this->method = $method;
        $this->parts = $parts;
        $this->variables = $variables;
        $this->handler = $handler;
        $this->middlewares = $middlewares;
    }

}