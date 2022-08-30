<?php

namespace Framework\Store;

use ReflectionClass;

/**
 * Ett skräddarsytt objekt för att transformera data innan den visas på ett annat ställe.
 */
class CustomDataObject
{

    private const IGNORE_FIELD = "[JsonIgnore]";

    /**
     * Hämtar en array med transformerad data.
     * @return array key-value array med datan.
     */
    public function getData(): array
    {
        $class = new ReflectionClass(get_class($this));
        $props = $class->getProperties();

        $data = array();
        foreach ($props as $prop) {
            $name = $prop->name;
            $value = $prop->getValue($this);

            if ($value instanceof CustomDataObject) {
                $value = $value->getData();
            }

            if(str_contains($prop->getDocComment(), self::IGNORE_FIELD)){ // Om kommentaren innehåller ignore regeln
                continue;
            }

            $data[$name] = $value;
        }

        return $data;
    }

}