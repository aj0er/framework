<?php

namespace Framework\Store;

use Framework\Mapping\ObjectMapper;
use PDO;

/**
 * En {@link SQLStore} som ansluter till SQLite.
 */
class SQLiteStore extends SQLStore
{

    function __construct(ObjectMapper $mapper, string $path, string $tableName, string $class)
    {
        parent::__construct($mapper, new PDO(dsn: "sqlite:" . $path, options: array(
            PDO::ATTR_PERSISTENT => false
        )), $tableName, $class);
    }

}