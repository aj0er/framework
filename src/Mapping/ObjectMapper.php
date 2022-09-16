<?php

namespace Framework\Mapping;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
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

            $arrayContentTypes = $this->parseArrayContentTypes($constructor);

            $params = $constructor->getParameters();
            $paramInstances = array();
            foreach ($params as $param) {
                $name = $param->getName();
                $value = $data[$name] ?? null;

                if ($value === null) {
                    $allowsNull = $param->allowsNull();
                    $isOptional = $param->isOptional();

                    if(!$isOptional && !$allowsNull){
                        error_log("\nUnable to map object constructor " . $reflectionClass->getName() . ":\nMissing required parameter \"" . $name . "\"");
                        return null;
                    }

                    if($allowsNull) // Om parametern tillåter null, sätt det
                        array_push($paramInstances, null);
          
                } else {
                    $mapped = $this->mapParam($param, $value, $arrayContentTypes);
                    if($mapped !== null) {
                        array_push($paramInstances, $mapped);
                    } else {
                        error_log("Unable to map object constructor, invalid value for \"" . $name . "\" provided.");
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
     * Försöker parsa typen för innehållet av en array genom att läsa dokumentationen för en
     * konstruktor. Detta är viktigt då PHP inte tillåter typning för arrayer.
     * 
     * Följer formatet: @var array users {@link App\Entities\User}
     * 
     * @param ReflectionMethod $method Metod att parsa dokumenation för (oftast konstruktor)
     */
    private function parseArrayContentTypes(ReflectionMethod $method): array {
        $doc = $method->getDocComment();
        $lines = explode("\n", $doc);
        $defs = [];

        foreach($lines as $line){
            $varPos = strpos($line, "@var");
            if($varPos === false){ // Om ingen @var-deklaration finns kan vi ignora denna rad.
                continue;
            }

            $line = substr(trim($line), $varPos);
            $parts = explode(" ", $line);

            if(count($parts) < 4){
                continue;
            }

            if($parts[0] === "array"){
                $paramName   = $parts[1];
                $linkedClass = $parts[3];
                $defs[$paramName] = substr($linkedClass, 0, strlen($linkedClass) - 1);
            }
        }

        return $defs;
    }

    /**
     * Mappar en param/property med ett värde.
     * I de flesta fall krävs ingen konvertering, men i t.ex. enums måste enum-instansen 
     * konverteras från en sträng innan vi kan sätta den.
     */
    private function mapParam($param, $value, $arrayContentTypes = null){
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

        // Om typen är array måste vi veta typen av innehållet för att kunna mappa fullständigt.
        if($type->getName() == "array"){
            if($arrayContentTypes == null || !isset($arrayContentTypes[$param->getName()])){
                error_log("No type to map array content!");
                return null;
            }

            if(is_string($value)){
                $value = json_decode($value, true);
            }

            $className = $arrayContentTypes[$param->getName()];

            $arr = [];
            foreach($value as $element){ // Mappa innehållet av arrayen
                $mapped = $this->mapObjectConstructor($element, new ReflectionClass($className));
                array_push($arr, $mapped);
            }

            return $arr;
        }

        return $value;
    }

}