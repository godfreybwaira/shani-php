<?php

/**
 * Description of SQLQuery
 *
 * @author coder
 */

namespace features\persistence\sql {

    use features\ds\map\ReadMap;
    use features\persistence\QueryAggregate;
    use features\persistence\DBDriver;
    use features\persistence\QueryInterface;
    use features\persistence\QueryFilter;

    final class SQLQuery implements QueryInterface
    {

        public readonly \PDO $pdo;
        private bool $escape = true;

        public function __construct(DBDriver $driver, string $database, string $host = null, ?int $port = null, ?string $username = null, ?string $password = null)
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

        private static function getConnectionString(DBDriver $driver, string $database, ?string $host, ?int $port): string
        {
            return match ($driver) {
                DBDriver::MYSQL,
                DBDriver::POSTGRES,
                DBDriver::SYBASE,
                DBDriver::DBLIB,
                DBDriver::MSSQL => $driver->value . ':host=' . $host . ':' . $port . ';dbname=' . $database,
                DBDriver::ORACLE => $driver->value . ':dbname=//' . $host . ':' . $port . '/' . $database,
                DBDriver::SQLITE => $driver->value . ':' . $database,
                DBDriver::SQL_SERVER => $driver->value . ':Server=' . $host . ',' . $port . ';Database=' . $database,
                DBDriver::ODBC => $driver->value . ':Driver=FreeTDS;Server=' . $host . ',' . $port . ';Database=' . $database
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

        public function delete(string $collection, QueryFilter $where, ?int $limit = null): int
        {
            $sql = 'DELETE FROM ' . $collection . $where;
            if (!empty($limit)) {
                $sql .= ' LIMIT ' . $limit;
            }
            return $this->run($sql, $where?->getBindings());
        }

        public function escapeHtml(bool $escape): QueryInterface
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
                    $paramName = ':p' . $index;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $row->jsonSerialize()[$col] ?? null;
                }
                $valueSets[] = '(' . implode(',', $placeholders) . ')';
            }
            $sql = 'INSERT INTO ' . $collection . '(' . implode(',', $columns) . ')VALUES' . implode(',', $valueSets);
            $this->beginTransaction();
            $result = $this->run($sql, $params) === count($object);
            return $this->endTransaction($result);
        }

        public function query(string $query, ?array $params = []): \Generator
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

        public function run(string $query, ?array $params = []): int
        {
            $statement = $this->processQuery($query, $params);
            $statement->closeCursor();
            return $statement->rowCount();
        }

        public function findAll(string $collection, ?QueryFilter $where = null, ?int $limit = null, int $page = 1): \Generator
        {
            $sql = 'SELECT * FROM ' . $collection . $where;
            if (!empty($limit) && $page > 0) {
                $pageNumber = ($page - 1) * $limit;
                $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $pageNumber;
            }
            return $this->query($sql, $where?->getBindings());
        }

        public function getAll(string $collection, ?QueryFilter $where = null, ?int $limit = null, int $page = 1): array
        {
            $rows = $this->findAll($collection, $where, $limit, $page);
            $records = [];
            foreach ($rows as $row) {
                $records[] = $row;
            }
            return $records;
        }

        public function getOne(string $collection, ?QueryFilter $where = null): ?ReadMap
        {
            foreach ($this->findAll($collection, $where, limit: 1) as $row) {
                return $row;
            }
            return null;
        }

        public function update(string $collection, \JsonSerializable $object, ?QueryFilter $where = null, ?int $limit = null): int
        {
            $data = $object->jsonSerialize();
            if (empty($data)) {
                return 0;
            }
            $setPrefix = 's_';
            $set = SQLResultGroup::createClause($data, 'SET', ',', $setPrefix);
            $sql = 'UPDATE ' . $collection . $set . $where;
            $filters = [];
            $values = $where?->getBindings() ?? [];
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

        public function exists(string $collection, ?QueryFilter $where = null): bool
        {
            $sql = 'SELECT 1 FROM ' . $collection . $where . ' LIMIT 1';
            $statement = $this->processQuery($sql, $where?->getBindings());
            return (bool) $statement->fetchColumn();
        }

        public function aggregate(string $collection): QueryAggregate
        {
            return SQLAggregate::getInstance($this, $collection);
        }
    }

}