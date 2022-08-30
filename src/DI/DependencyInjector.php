<?php

namespace Framework\DI;

use ReflectionClass;
use ReflectionException;

/**
 * Extremt simpel dependency-injektion, cachar instanser av objekt eller konstruerar nya
 * objekt genom att kolla på klassens konstruktor-parametrar.
 *
 * Exempel:
 * PostController kräver PostService som kräver PostStore.
 * PostStore läggs till med {@link DependencyInjector::addInstance()} eftersom den är den sista dependencien.
 * Denna klassen injectar alla klasser med de dependencies som de kräver.
 */
class DependencyInjector
{

    private array $instances;

    /**
     * Lägger till en instans av ett objekt i cachen.
     * Anropar get_class för att få klassens unika namn.
     *
     * @param $instance mixed Instans på klassen.
     */
    function addInstance(mixed $instance)
    {
        $this->addNamedInstance(get_class($instance), $instance);
    }

    /**
     * Lägger till en instans av ett objekt i cachen då namnet på klassen är känt.
     *
     * @param $className string Namnet på klassen.
     * @param $instance mixed Instansen på klassen.
     */
    function addNamedInstance(string $className, mixed $instance)
    {
        $this->instances[$className] = $instance;
    }

    /**
     * Försöker hämta en instans av ett objekt av den specificerade typen.
     * Om det inte är sparat försöker vi skapa en ny instans genom att kolla vilka konstruktor-parametrar
     *
     * @param $class
     * @return mixed Instansen av objektet eller null om det inte går att konstruera ett nytt sådant objekt.
     */
    function getInstance($class): mixed
    {
        $instance = $this->instances[$class] ?? null;

        if (isset($instance)) {
            return $instance; // Instansen är redan skapad och cachad.
        } else {
            try {
                $reflection = new ReflectionClass($class);
                $constructor = $reflection->getConstructor();
                if (!isset($constructor)) { // Klassen har ingen konstruktor och kräver därför ingen injektion. Skapa den direkt.
                    return new $class();
                }

                $params = $constructor->getParameters();
                $paramInstances = array();
                foreach ($params as $param) {
                    $type = $param->getType();
                    if ($type == null)
                        return null; // Parametern har ingen typ och vi vet därför inte vad vi behöver injecta den med.

                    $paramInstance = $this->getInstance($type->getName()); // Anropa rekursivt för att få subdependencies.
                    if (isset($paramInstance)) {
                        array_push($paramInstances, $paramInstance);
                    } else {
                        return null;
                    }
                }

                $created = $reflection->newInstanceArgs($paramInstances);
                $this->instances[$class] = $created;
                return $created; // Skapa den nya instansen med de dependencies som vi nu hämtat.
            } catch (ReflectionException $ex) {
                error_log($ex);
                return null;
            }
        }
    }

}