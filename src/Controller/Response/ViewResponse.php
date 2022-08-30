<?php

namespace Framework\Controller\Response;

/**
 * Respons för en vy som ska renderas med Twig.
 */
class ViewResponse
{

    /**
     * @var string Namn för vyn utan filändelse.
     */
    public string $name;

    /**
     * @var array Model med data som ska renderas i vyn.
     */
    public array $model;

    /**
     * Konstruerar en ny ViewResponse.
     *
     * @param string $name Namn för vyn utan filändelse.
     * @param array $model Model med data som ska renderas i vyn.
     */
    public function __construct(string $name, array $model)
    {
        $this->name = $name;
        $this->model = $model;
    }

}