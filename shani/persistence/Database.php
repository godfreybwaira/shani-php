<?php

/**
 * Description of Database
 *
 * @author coder
 */

namespace shani\persistence {

    use lib\map\MutableMap;
    use shani\exceptions\ServerException;

    final class Database
    {

        public readonly \PDO $pdo;
        private bool $escape = true;

        public function __construct(string $driver, string $database, string $host = null, ?int $port = null, ?string $username = null, ?string $password = null)
        {
            $connectionString = self::getConnectionString($driver, $database, $host, $port);
            $this->pdo = new \PDO($connectionString, $username, $password);
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
         * Execute SQL query and all rows (if available) found
         * @param string $query A query to execute
         * @param array|null $data
         * @return MutableMap Iterable object contains rows returned as the result of SQL query.
         */
        public function get(string $query, ?array $data = null): MutableMap
        {
            $result = $this->processQuery($query, $data);
            $rows = $result->fetchAll(\PDO::FETCH_ASSOC);
            $result->closeCursor();
            if (!empty($rows) && $this->escape) {
                self::escapeHTML($rows);
            }
            return new MutableMap($rows);
        }

        private static function getConnectionString(string $driver, string $database, ?string $host, ?int $port): string
        {
            switch ($driver) {
                case 'mysql':
                case 'pgsql':
                case 'sybase':
                case 'mssql':
                    return $driver . ':host=' . $host . ':' . $port . ';dbname=' . $database;
                case 'dblib': //for sqlserver & sybase
                    return 'dblib:host=' . $host . ':dbname=' . $database;
                case 'oci': //for oracle
                    return 'oci:dbname=//' . $host . ':' . $port . '/' . $database;
                case 'sqlite':
                    return 'sqlite:' . $database;
                case 'sqlsrv'://for sqlserver
                    return 'sqlsrv:Server=' . $host . ';Database=' . $database;
                case 'odbc'://for sqlserver
                    return 'odbc:Driver=FreeTDS;Server=' . $host . ':' . $port . ';Database=' . $database;
            }
            throw new ServerException('Driver "' . $driver . '" not supported');
        }
    }

}