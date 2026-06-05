<?php

/**
 * Description of SQLDatabase
 *
 * @author coder
 */

namespace features\persistence\sql {

    use features\ds\map\ReadMap;
    use features\persistence\AggregateInterface;
    use features\persistence\DatabaseDriver;
    use features\persistence\DatabaseInterface;
    use features\persistence\FilterClause;

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

        private function processQuery(string &$query, ?array $params): \PDOStatement
        {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement;
        }

        public function beginTransaction(): bool
        {
            return $this->pdo->inTransaction() || $this->pdo->beginTransaction();
        }

        public function endTransaction(bool $condition): bool
        {
            if ($this->pdo->inTransaction()) {
                if ($condition) {
                    $this->commit();
                } else {
                    $this->rollback();
                }
            }
            return $condition;
        }

        public function commit(): void
        {
            $this->pdo->commit();
        }

        public function rollback(): void
        {
            $this->pdo->rollBack();
        }

        public function delete(string $collection, FilterClause $where, ?int $limit = null): int
        {
            $sql = 'DELETE FROM ' . $collection . $where;
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit;
            }
            return $this->run($sql, $where?->getValuePair());
        }

        public function escapeHtml(bool $escape): DatabaseInterface
        {
            $this->escape = $escape;
            return $this;
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

        public function insertAll(string $collection, \JsonSerializable ...$object): bool
        {
            $columns = array_keys($object[0]->jsonSerialize()); //Get column names from first row (all rows must have same structure)
            $valueSets = [];
            $params = [];
            foreach ($object as $index => $row) {
                $placeholders = [];
                foreach ($columns as $col) {
                    $paramName = ':' . $col . $index;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $row->jsonSerialize()[$col] ?? null;
                }
                $valueSets[] = '(' . implode(',', $placeholders) . ')';
            }
            $colList = implode(',', $columns);
            $sql = 'INSERT INTO ' . $collection . '(' . $colList . ')VALUES' . implode(',', $valueSets);
            $this->beginTransaction();
            $result = $this->run($sql, $params) === count($object);
            return $this->endTransaction($result);
        }

        public function query(string $query, array $params = []): \Generator
        {
            $statement = $this->processQuery($query, $params);
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->escape) {
                    self::escapeHtmlChars($row);
                }
                yield new ReadMap($row);
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
                $results[] = new ReadMap($row);
            }
            $statement->closeCursor();
            return $results;
        }

        public function run(string $query, array $params = []): int
        {
            $statement = $this->processQuery($query, $params);
            $statement->closeCursor();
            return $statement->rowCount();
        }

        public function find(string $collection, ?FilterClause $where = null, ?int $limit = null, int $skip = 0): \Generator
        {
            $sql = 'SELECT * FROM ' . $collection . $where;
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $skip;
            }
            return $this->query($sql, $where?->getValuePair());
        }

        public function findAll(string $collection, ?FilterClause $where = null, ?int $limit = null, int $skip = 0): array
        {
            $rows = [];
            $results = $this->find($collection, $where, $limit, $skip);
            foreach ($results as $row) {
                $rows[] = $row;
            }
            return $rows;
        }

        public function findOne(string $collection, ?FilterClause $where = null): ?ReadMap
        {
            foreach ($this->find($collection, $where, limit: 1) as $row) {
                return $row;
            }
            return null;
        }

        public function update(string $collection, \JsonSerializable $object, ?FilterClause $where = null, ?int $limit = null): int
        {
            $data = $object->jsonSerialize();
            if (empty($data)) {
                return 0;
            }
            $setPrefix = 's_';
            $set = SQLClause::createClause($data, 'SET', ',', $setPrefix);
            $sql = 'UPDATE ' . $collection . $set . $where;
            $filters = [];
            $values = $where?->getValuePair() ?? [];
            foreach ($values as $key => $value) {
                $filters[$key] = $value;
            }
            foreach ($data as $key => $value) {
                $filters[$setPrefix . $key] = $value;
            }
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit;
            }
            return $this->run($sql, $filters);
        }

        public function exists(string $collection, ?FilterClause $where = null): bool
        {
            $sql = 'SELECT 1 FROM ' . $collection . $where . ' LIMIT 1';
            $statement = $this->processQuery($sql, $where?->getValuePair());
            return (bool) $statement->fetchColumn();
        }

        public function aggregate(string $collection): AggregateInterface
        {
            return SQLAggregate::getInstance($this, $collection);
        }
    }

}