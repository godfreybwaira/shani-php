<?php

/**
 * Description of DatabaseConnection
 *
 * @author coder
 */

namespace shani\persistence {

    use lib\ds\map\ReadableMap;

    final class DatabaseConnection
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

        private function processQuery(string &$query, ?array $data): \PDOStatement
        {
            $result = $this->pdo->prepare($query);
            $result->execute($data);
            return $result;
        }

        /**
         * Whether to escape HTML characters on result set or not.
         * @param bool $escape When true, HTML characters will be escaped
         * @return self
         */
        public function escapeHtml(bool $escape): self
        {
            $this->escape = $escape;
            return $this;
        }

        /**
         * Execute SQL query and return number of rows affected
         * @param string $query A query to run
         * @param array|null $data
         * @return int Number of rows affected
         */
        public function run(string $query, ?array $data = null): int
        {
            $result = $this->processQuery($query, $data);
            $result->closeCursor();
            return $result->rowCount();
        }

        /**
         * Execute SQL query and all rows (if available) found. For a large data set
         * (more than 1 row) use <code>loop</code> for efficiency.
         * @param string $query A query to execute
         * @param array|null $data
         * @return ReadableMap Iterable object contains rows returned as the result of SQL query.
         */
        public function get(string $query, ?array $data = null): ReadableMap
        {
            $result = $this->processQuery($query, $data);
            $rows = $result->fetchAll(\PDO::FETCH_ASSOC);
            $result->closeCursor();
            if (!empty($rows) && $this->escape) {
                self::escapeHtmlChars($rows);
            }
            return new ReadableMap($rows);
        }

        /**
         * Execute SQL query and fetch all rows (if available). This method is memory
         * efficient as it fetches rows on demand
         * @param string $query A query to execute
         * @param array|null $data
         * @return \Generator Iterable object of ReadableMap contains rows returned as the result of SQL query.
         */
        public function collect(string $query, ?array $data = null): \Generator
        {
            $result = $this->processQuery($query, $data);
            while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->escape) {
                    self::escapeHtmlChars($row);
                }
                yield new ReadableMap($row);
            }
            $result->closeCursor();
        }

        private static function getConnectionString(DatabaseDriver $driver, string $database, ?string $host, ?int $port): string
        {
            switch ($driver) {
                case DatabaseDriver::MYSQL:
                case DatabaseDriver::POSTGRES:
                case DatabaseDriver::SYBASE:
                case DatabaseDriver::MSSQL:
                    return $driver->value . ':host=' . $host . ':' . $port . ';dbname=' . $database;
                case DatabaseDriver::DBLIB:
                    return 'dblib:host=' . $host . ':dbname=' . $database;
                case DatabaseDriver::ORACLE:
                    return 'oci:dbname=//' . $host . ':' . $port . '/' . $database;
                case DatabaseDriver::SQLITE:
                    return 'sqlite:' . $database;
                case DatabaseDriver::SQL_SERVER:
                    return 'sqlsrv:Server=' . $host . ';Database=' . $database;
                case DatabaseDriver::ODBC:
                    return 'odbc:Driver=FreeTDS;Server=' . $host . ':' . $port . ';Database=' . $database;
            }
        }
    }

}