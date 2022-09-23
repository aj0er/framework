<?php

namespace Framework\Store;

use Framework\Mapping\ObjectMapper;
use PDO;

/**
 * En {@link SQLStore} som ansluter till MySQL.
 */
class MySQLStore extends SQLStore
{

    function __construct(ObjectMapper $mapper, string $host, string $database, 
                         string $username, string $password, string $tableName, string $class)
    {
        parent::__construct($mapper, new PDO("mysql:host=$host;dbname=$database", $username, $password, array(
            PDO::ATTR_PERSISTENT => false
        )), $tableName, $class);
    }

}