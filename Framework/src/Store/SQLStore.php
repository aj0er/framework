<?php

namespace Framework\Store;

use BackedEnum;
use Exception;
use Framework\Mapping\ObjectMapper;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionException;

/**
 * Helper-klass för att tala SQL med en databas och göra olika transaktioner för objekt.
 */
class SQLStore
{

    private PDO $db;
    private string $tableName;
    private ReflectionClass $class;
    private ObjectMapper $mapper;

    private const SEPARATOR = ", ";

    /**
     * @param ObjectMapper $mapper Mapper som kan mappa datan till objektet.
     * @param PDO $handle PDO-instansen att interagera med.
     * @param string $tableName Tabellens namn där datan lagras.
     * @param string $class Klass som denna store hanterar.
     */
    function __construct(ObjectMapper $mapper, PDO $handle, string $tableName, string $class)
    {
        $this->mapper = $mapper;
        $this->tableName = $tableName;
        $this->db = $handle;

        try {
            $this->class = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            error_log($e);
        }
    }

    /**
     * Skapar en tabell (om den inte tidigare finns).
     *
     * @param array $columns namn => typ array med de kolumner som tabellen ska innehålla.
     * Exempel: "id" => "varchar(16)"
     */
    protected function createTable(array $columns)
    {
        $i = 0;
        $columnQuery = "";
        foreach ($columns as $column => $value) {
            if ($i != 0)
                $columnQuery .= self::SEPARATOR;

            $columnQuery .= ($column . " " . $value);
            $i++;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS " . $this->tableName . " (" . $columnQuery . ");");
    }

    /**
     * Lagrar ett objekt i databasen.
     * @param mixed $object objekt att lagra.
     */
    protected function insert(mixed $object)
    {
        $params = (array)$object; // Gör om objektet till en array, av en klass blir då alla properties en del av arrayen.

        $columnString = "";
        $valueString = "";
        $i = 0;

        foreach ($params as $column => $value) {
            if ($i != 0) {
                $columnString .= self::SEPARATOR;
                $valueString .= self::SEPARATOR;
            }

            $columnString .= $column;
            $valueString .= "?";
            $i++;
        }

        $query = "INSERT INTO " . $this->tableName . " (" . $columnString . ") VALUES(" . $valueString . ")";
        $this->query($query, array_values($params));
    }

    /**
     * Konverterar ett värde till ett SQL-vänligt värde.
     * @param $value mixed Värde att konvertera.
     * @return mixed SQL-vänligt värde.
     */
    private static function convertValue(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    /**
     * Exekverar en SQL-förfrågan med namngivna parametrar och mappar resultatet till ett objekt.
     * @param string $query Förfrågan att exekvera.
     * @param array $params key => value array med parametrar att sätta in i förfrågan.
     * @return mixed Det första matchade objektet eller null om inget sådant finns.
     */
    protected function queryOne(string $query, array $params = []): mixed
    {
        return $this->queryNamed($query, $params)[0] ?? null;
    }

    /**
     * Exekverar en SQL-förfrågan och mappar resultatet till objekt.
     * @param string $query Förfrågan att exekvera.
     * @param array $params Parametrar att sätta in i förfrågan, måste följa ordningen av "?" i förfrågan.
     * @return array Array med resulterande mappade objekt.
     */
    protected function query(string $query, array $params = []): array
    {
        $stmt = $this->execute($query, $params);
        $res = $stmt->fetchAll();

        return $this->getResponse($res);
    }

    /**
     * Hanterar resultatet från databasen och mappar det till objektet som denna store hanterar.
     * @param array $res Array med underarrayer med datan i.
     * @return array Array med mappad data.
     */
    private function getResponse(array $res): array {
        return array_map(function ($row) {
            $mapped = $this->mapper->mapObjectConstructor($row, $this->class);
            if($mapped != null){
                $this->onFetched($mapped); // Notifiera stores som extendar metoden "onFetched" att ett nytt objekt blivit hämtat.
            }

            return $mapped;
        }, $res);
    }

    /**
     * Exekverar en SQL-förfrågan med namngivna parametrar och mappar resultatet till objekt.
     * @param string $query Förfrågan att exekvera.
     * @param array $params key => value array med parametrar att sätta in i förfrågan.
     * @return array Array med resulterande mappade objekt.
     */
    protected function queryNamed(string $query, array $params = []): array
    {
        $stmt = $this->executeNamed($query, $params);
        $res = $stmt->fetchAll();

        return $this->getResponse($res);
    }

    /**
     * Exekverar en SQL-förfrågan.
     * @param string $query Förfrågan att exekvera.
     * @param array $params Parametrar att sätta in i förfrågan, följer ordningen av "?" i förfrågan.
     * @return PDOStatement Exekverad PDOStatement.
     */
    protected function execute(string $query, array $params = []): PDOStatement
    {
        $params = $this->convertValues($params);

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Konverterar värden i en array med {@link convertValue}.
     * @param array $params Array att konvertera.
     * @return array Array med konverterade värden.
     */
    private function convertValues(array $params): array
    {
        foreach ($params as $key => $value) {
            $params[$key] = $this->convertValue($value);
        }

        return $params;
    }

    /**
     * Exekverar en SQL-förfrågan med namngivna parametrar.
     * @param string $query Förfrågan att exekvera.
     * @param array $params key => value array med parametrar att sätta in i förfrågan.
     * @return PDOStatement Exekverad PDOStatement.
     */
    protected function executeNamed(string $query, array $params = []): PDOStatement
    {
        $params = $this->convertValues($params);

        $stmt = $this->prepareNamedQuery($query, $params);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Förbereder en SQL-förfrågan med namngivna parametrar.
     * @param string $query Förfrågan att förbereda.
     * @param array $params key => value array med parametrar att sätta in i förfrågan.
     * @return PDOStatement Förberedd PDOStatement.
     */
    private function prepareNamedQuery(string $query, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($query);
        foreach ($params as $column => $value) {
            $stmt->bindParam($column, $value);
            unset($value);
        }

        return $stmt;
    }

    /**
     * Metod som anropas då ett objekt funnits i en förfrågan och mappats.
     * @param $data mixed Nymappat objekt som hittats i databasen.
     */
    protected function onFetched(mixed $data){}

    // Hämtad från https://stackoverflow.com/a/15875555
    public static function generateUuid(): string
    {
        try {
            $data = random_bytes(16);
        } catch (Exception $e) {
            error_log($e);
            return "";
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}