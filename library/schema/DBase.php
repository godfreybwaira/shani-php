<?php

/**
 * Description of DBase
 *
 * @author coder
 */

namespace library\schema {

    final class DBase
    {

        private \PDO $pdo;

        public function __construct(string $driver, string $database, string $host, ?int $port = null, ?string $username = null, ?string $password = null)
        {
            try {
                $connectionString = self::getConnectionString($driver, $database, $host, $port);
                $this->pdo = new \PDO($connectionString, $username, $password);
            } catch (\Exception $ex) {
                echo 'Database connection failed! ' . $ex->getMessage();
            }
        }

        private static function escapeHTML(&$var): void
        {
            if (is_string($var)) {
                $var = htmlspecialchars($var, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            } elseif (is_array($var)) {
                foreach ($var as &$value) {
                    self::escapeHTML($value);
                }
            }
        }

        private function processQuery(string &$query, ?array $data): \PDOStatement
        {
            try {
                $result = $this->pdo->prepare($query);
                $result->execute($data);
                return $result;
            } catch (\Exception $ex) {
                throw $ex;
            }
        }

        /**
         * Execute SQL query and return number of rows affected
         * @param string $query A query to run
         * @param array|null $data Data to run with query
         * @return int Number of rows affected
         */
        public function runQuery(string $query, ?array $data = null): int
        {
            $result = $this->processQuery($query, $data);
            $result->closeCursor();
            return $result->rowCount();
        }

        /**
         * Execute SQL query and all rows (if available) found
         * @param string $query A query to execute
         * @param array|null $data Data to run with query
         * @param bool $escapeHtml Whether to escape HTML special characters on SQL
         * output or not
         * @return array Rows returned as the result of SQL query.
         * @see DBase::getResultAsTable()
         */
        public function getResult(string $query, ?array $data = null, bool $escapeHtml = true): array
        {
            $result = $this->processQuery($query, $data);
            $rows = $result->fetchAll(\PDO::FETCH_ASSOC);
            $result->closeCursor();
            if (!empty($rows) && $escapeHtml) {
                self::escapeHTML($rows);
            }
            return $rows;
        }

        /**
         * Execute SQL query and all rows (if available) found as table like array
         * @param string $query A query to execute
         * @param array|null $data Data to run with query
         * @param bool $escapeHtml Whether to escape HTML special characters on SQL
         * output or not
         * @return array Rows returned as the result of SQL query.
         * @see DBase::getResult()
         */
        public function getResultAsTable(string $query, array $headers, ?array $data = null, bool $escapeHtml = true): array
        {
            $rows = $this->getResult($query, $data, $escapeHtml);
            return \library\DataConvertor::array2table($rows, $headers);
        }

        private static function getConnectionString(string $driver, string $database, string $host, ?int $port): string
        {
            switch ($driver) {
                case 'mysql':
                case 'pgsql':
                case 'sybase':
                case 'mssql':
                    return $driver . ':host=' . $host . ':' . $port . ';dbname=' . $database;
                case 'dblib': //for sqlserver & sybase
                    return "dblib:host=$host:dbname=" . $database;
                case 'oci': //for oracle
                    return 'oci:dbname=//' . $host . ':' . $port . '/' . $database;
                case 'sqlite':
                    return "sqlite:$database";
                case 'sqlsrv'://for sqlserver
                    return "sqlsrv:Server=$host;Database=" . $database;
                case 'odbc'://for sqlserver
                    return "odbc:Driver=FreeTDS;Server=$host:$port;Database=" . $database;
            }
            throw new \RuntimeException('Driver "' . $driver . '" not supported');
        }
    }

}