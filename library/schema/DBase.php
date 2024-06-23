<?php

/**
 * Description of DBase
 *
 * @author coder
 */

namespace library\schema {

    final class DBase
    {

        private $pdo;

        private function __construct(array &$con)
        {
            try {
                $dsn = static::{$con['driver']}($con);
                $this->pdo = new \PDO($dsn, $con['user'], $con['pass']);
            } catch (\Exception $ex) {
                echo 'Database connection failed! ' . $ex->getMessage();
            }
        }

        public static function connect(array $connection): DBase
        {
            return new self($connection);
        }

        private static function mysql(array &$con): string
        {
            return "mysql:host={$con['host']}:{$con['port']};dbname={$con['dbname']};charset={$con['charset']}";
        }

        private static function pgsql(array &$con): string
        {
            return "pgsql:host={$con['host']}:{$con['port']};dbname={$con['dbname']};charset={$con['charset']}";
        }

        private static function sqlite(array &$con): string
        {
            return "sqlite:{$con['dbname']}";
        }

        private static function odbc(array &$con): string
        {
            return "odbc:Driver=ODBC Driver 11 for SQL Server;Server={$con['host']}:{$con['port']};Database={$con['dbname']}";
        }

        private static function sybase(array &$con): string
        {
            return "sybase:host={$con['host']}:{$con['port']};dbname={$con['dbname']};charset={$con['charset']}";
        }

        private static function dblib(array &$con): string
        {
            return "dblib:version=7.0;host={$con['host']}:{$con['port']};dbname={$con['dbname']};charset={$con['charset']}";
        }

        private static function mssql(array &$con): string
        {
            return "mssql:host={$con['host']}:{$con['port']};dbname={$con['dbname']};charset={$con['charset']}";
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

        public function runQuery(string $query, ?array $data = null): int
        {
            $result = $this->processQuery($query, $data);
            $result->closeCursor();
            return $result->rowCount();
        }

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

        public function getCompact(string $query, array $headers, ?array $data = null, bool $escapeHtml = true): array
        {
            $rows = $this->getResult($query, $data, $escapeHtml);
            return \library\DataConvertor::array2compact($rows, $headers);
        }
    }

}