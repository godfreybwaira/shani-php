<?php

/**
 * Description of SQLDatabase
 *
 * @author coder
 */

namespace features\persistence {

    use features\ds\map\ReadableMap;

    final class SQLDatabase implements DatabaseInterface
    {

        public readonly \PDO $pdo;
        private bool $escape = true;

        public function __construct(DatabaseDriver $driver, string $database, string $host = null, ?int $port = null, ?string $username = null, ?string $password = null)
        {
            $connectionString = self::getConnectionString($driver, $database, $host, $port);
            $this->pdo = new \PDO($connectionString, $username, $password);
        }

        private static function escapeHtmlChars(&$var): void
        {
            if (is_string($var)) {
                $var = htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            } elseif (is_array($var)) {
                foreach ($var as &$value) {
                    self::escapeHtmlChars($value);
                }
            }
        }

        private static function getConnectionString(DatabaseDriver $driver, string $database, ?string $host, ?int $port): string
        {
            return match ($driver) {
                DatabaseDriver::MYSQL,
                DatabaseDriver::POSTGRES,
                DatabaseDriver::SYBASE,
                DatabaseDriver::DBLIB,
                DatabaseDriver::MSSQL => $driver->value . ':host=' . $host . ':' . $port . ';dbname=' . $database,
                DatabaseDriver::ORACLE => $driver->value . ':dbname=//' . $host . ':' . $port . '/' . $database,
                DatabaseDriver::SQLITE => $driver->value . ':' . $database,
                DatabaseDriver::SQL_SERVER => $driver->value . ':Server=' . $host . ',' . $port . ';Database=' . $database,
                DatabaseDriver::ODBC => $driver->value . ':Driver=FreeTDS;Server=' . $host . ',' . $port . ';Database=' . $database
            };
        }

        private static function createClause(array $params, string $clause, string $join, ?string $prefix = null): ?string
        {
            $filters = [];
            foreach ($params as $key => $value) {
                $filters[] = $key . '=:' . $prefix . $key;
            }
            if (!empty($filters)) {
                return " $clause " . implode($join, $filters);
            }
            return null;
        }

        private function processQuery(string &$query, ?array $params): \PDOStatement
        {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement;
        }

        public function beginTransaction(): DatabaseInterface
        {
            $this->pdo->beginTransaction();
            return $this;
        }

        public function commit(): void
        {
            $this->pdo->commit();
        }

        public function delete(string $collection, array $where): int
        {
            $whereClause = self::createClause($where, 'WHERE', ' AND ');
            $sql = 'DELETE FROM ' . $collection . $whereClause;
            return $this->run($sql, $where);
        }

        public function escapeHtml(bool $escape): DatabaseInterface
        {
            $this->escape = $escape;
            return $this;
        }

        public function inTransaction(): bool
        {
            return $this->pdo->inTransaction();
        }

        public function insert(string $collection, \JsonSerializable $object): string|int
        {
            $data = $object->jsonSerialize();
            $columns = array_keys($data);
            $sql = 'INSERT INTO ' . $collection . '(' . implode(',', $columns);
            $sql .= ')VALUES(:' . implode(',:', $columns) . ')';
            $statement = $this->processQuery($sql, $data);
            $statement->closeCursor();
            return $this->pdo->lastInsertId();
        }

        public function insertAll(string $collection, \JsonSerializable $object): bool
        {
            $data = $object->jsonSerialize();
            $columns = array_keys($data[0]); //Get column names from first row (all rows must have same structure)
            $valueSets = [];
            $params = [];
            foreach ($data as $index => $row) {
                $placeholders = [];
                foreach ($columns as $col) {
                    $paramName = ':' . $col . $index;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $row[$col] ?? null;
                }
                $valueSets[] = '(' . implode(',', $placeholders) . ')';
            }
            $colList = implode(',', $columns);
            $sql = 'INSERT INTO ' . $collection . '(' . $colList . ')VALUES' . implode(',', $valueSets);
            $transact = !$this->inTransaction();
            if ($transact) {
                $this->beginTransaction();
            }
            $statement = $this->processQuery($sql, $params);
            $result = $statement->rowCount() === count($data);
            $statement->closeCursor();
            if ($transact) {
                if ($result) {
                    $this->commit();
                } else {
                    $this->rollback();
                }
            }
            return $result;
        }

        public function query(string $query, array $params = []): \Generator
        {
            $statement = $this->processQuery($query, $params);
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->escape) {
                    self::escapeHtmlChars($row);
                }
                yield new ReadableMap($row);
            }
            $statement->closeCursor();
        }

        public function queryAll(string $query, array $params = []): array
        {
            $results = [];
            $statement = $this->processQuery($query, $params);
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->escape) {
                    self::escapeHtmlChars($row);
                }
                $results[] = new ReadableMap($row);
            }
            $statement->closeCursor();
            return $results;
        }

        public function rollback(): void
        {
            $this->pdo->rollBack();
        }

        public function run(string $query, array $params = []): int
        {
            $statement = $this->processQuery($query, $params);
            $statement->closeCursor();
            return $statement->rowCount();
        }

        public function find(string $collection, array $where = [], ?int $limit = null, int $skip = 0): \Generator
        {
            $whereClause = self::createClause($where, 'WHERE', ' AND ');
            $sql = 'SELECT * FROM ' . $collection . $whereClause;
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $skip;
            }
            return $this->query($sql, $where);
        }

        public function findAll(string $collection, array $where = [], ?int $limit = null, int $skip = 0): array
        {
            $rows = [];
            $results = $this->find($collection, $where, $limit, $skip);
            foreach ($results as $row) {
                $rows[] = new ReadableMap($row);
            }
            return $rows;
        }

        public function findOne(string $collection, array $where = []): ?ReadableMap
        {
            foreach ($this->find($collection, $where, limit: 1) as $row) {
                return new ReadableMap($row);
            }
            return null;
        }

        public function update(string $collection, \JsonSerializable $object, array $where): int
        {
            $data = $object->jsonSerialize();
            if (empty($data)) {
                return 0;
            }
            $setPrefix = 's_';
            $wherePrefix = 'w_';
            $set = self::createClause($data, 'SET', ',', $setPrefix);
            $whereClause = self::createClause($where, 'WHERE', ' AND ', $wherePrefix);
            $sql = 'UPDATE ' . $collection . ' SET ' . $set . $whereClause;
            $filters = [];
            foreach ($where as $key => $value) {
                $filters[$wherePrefix . $key] = $value;
            }
            foreach ($data as $key => $value) {
                $filters[$setPrefix . $key] = $value;
            }
            return $this->run($sql, $filters);
        }

        public function count(string $collection, array $where = []): int
        {
            $whereClause = self::createClause($where, 'WHERE', ' AND ');
            $sql = 'SELECT COUNT(*) AS c FROM ' . $collection . $whereClause;
            $statement = $this->processQuery($sql, $where);
            return (int) $statement->fetchColumn();
        }

        public function exists(string $collection, array $where = []): bool
        {
            $whereClause = self::createClause($where, 'WHERE', ' AND ');
            $sql = 'SELECT 1 FROM ' . $collection . $whereClause;
            $statement = $this->processQuery($sql, $where);
            return (bool) $statement->fetchColumn();
        }
    }

}