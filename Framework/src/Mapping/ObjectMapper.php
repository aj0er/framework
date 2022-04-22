<?php

namespace Framework\Mapping;

use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Klass som mappar data till ett objekt på olika sätt.
 */
class ObjectMapper
{

    /**
     * Mappar ett objekts properties med data.
     * En klass kan t.ex. ha properties som "username", "password" vilket sätts från data-parametern.
     * 
     * @param mixed $data Data att mappa med.
     * @param object $instance Instans att mappa datan på.
     */
    function mapObjectProperties(mixed $data, object $instance): bool
    {
        try {
            $clazz = new ReflectionClass($instance);
            $properties = $clazz->getProperties();

            foreach ($properties as $param) {
                $name = $param->getName();
                $value = $data[$name] ?? null;

                if ($value != null) {
                    $mapped = $this->mapParam($param, $value);
                    if($mapped !== null) {
                        $param->setValue($instance, $mapped);
                    } else {
                        error_log("Unable to map object properties, invalid value for \"" . $name . "\" provided.");
                        return false;
                    }
                }
            }
        } catch (ReflectionException $ex){
            error_log($ex->getMessage());
        }

        return true;
    }

    /**
     * Skapar en instans av ett objekt genom att mappa konstruktorn med data.
     *
     * @param mixed $data Data att mappa med.
     * @param mixed $reflectionClass Klass att mappa.
     * @return object|null Mappat objekt eller null om ett fel uppstod.
     */
    function mapObjectConstructor(mixed $data, ReflectionClass $reflectionClass): ?object
    {
        try {
            $constructor = $reflectionClass->getConstructor();
            if (!isset($constructor)) { // Klassen har ingen konstruktor och kräver därför inga parametrar.
                return $reflectionClass->newInstance();
            }

            $params = $constructor->getParameters();
            $paramInstances = array();
            foreach ($params as $param) {
                $name = $param->getName();
                $value = $data[$name] ?? null;

                if ($value === null) {
                    if ($param->allowsNull()) { // Parametern accepterar null värden, sätt det.
                        array_push($paramInstances, null);
                    } else {
                        error_log("Unable to map object constructor " . $reflectionClass->getName() . ": 
                            Missing required parameter \"" . $name . "\"");

                        return null;
                    }
                } else {
                    $mapped = $this->mapParam($param, $value);
                    if($mapped !== null) {
                        array_push($paramInstances, $mapped);
                    } else {
                        error_log("Unable to map object construcor, invalid value for \"" . $name . "\" provided.");
                        return null;
                    }
                }
            }

            return $reflectionClass->newInstanceArgs($paramInstances);
        } catch(ReflectionException $ex){
            error_log($ex->getMessage());
        }

        return null;
    }

    /**
     * Mappar en param/property med ett värde.
     * I de flesta fall krävs ingen konvertering, men i t.ex. enums måste enum-instansen 
     * konverteras från en sträng innan vi kan sätta den.
     */
    private function mapParam($param, $value){
        $type = $param->getType();
        if($type == null){
            return $value;
        }

        if(enum_exists($type)){
            try {
                // Anropa "from" metoden för att få enum objektet. T.ex. få Role::ADMIN från backed value "admin"
                return call_user_func(array($param->getType()->getName(), 'from'), $value);
            } catch (Throwable){
                return null;
            }
        }

        return $value;
    }

}