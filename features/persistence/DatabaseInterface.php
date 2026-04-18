<?php

/**
 * Description of DatabaseInterface
 * @author goddy
 *
 * Created on: Apr 18, 2026 at 8:04:10 AM
 */

namespace features\persistence {

    use features\ds\map\ReadableMap;

    /**
     * Unified Database Interface for PHP
     *
     * Supports both SQL (via PDO) and NoSQL
     * All CRUD operations automatically respect the active transaction when one is started.
     */
    interface DatabaseInterface
    {

        /**
         * Insert a single document/record
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param array $data Data to insert
         * @return string|int The inserted ID
         */
        public function insert(string $collection, array $data): string|int;

        /**
         * Insert multiple documents/records. This method is transaction-aware
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param array $data A @D array of data to insert
         * @return bool True if all data inserted, false otherwise
         */
        public function insertAll(string $collection, array $data): bool;

        /**
         * Update documents/records
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param array $data New data
         * @param array $where Query parameters (key => value pair)
         * @return int Number of modified documents
         */
        public function update(string $collection, array $data, array $where): int;

        /**
         * Delete documents/records
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param array $where Query parameters (key => value pair)
         * @return int Number of deleted documents
         */
        public function delete(string $collection, array $where): int;

        /**
         * Execute SQL query and fetch all rows (if available). This method is memory
         * efficient as it fetches rows on demand
         * @param string $query A query to execute
         * @param array $params Query parameters (key => value pair)
         * @return \Generator Iterable object of ReadableMap contains rows returned as the result of SQL query.
         * @see self::findAll
         */
        public function query(string $query, array $params = []): \Generator;

        /**
         * Execute query and returns all rows (if available) found. For a large data set
         * use <code>query</code> for efficiency.
         * @param string $query A query to execute
         * @param array $params Query parameters (key => value pair)
         * @return array Rows of readableMap object returned as the result of the query.
         * @see self::generateAll
         */
        public function queryAll(string $query, array $params = []): array;

        /**
         * Execute a query and return number of rows affected.
         * @param string $query A query to execute
         * @param array $params Query parameters (key => value pair)
         * @return int Number of rows affected
         */
        public function run(string $query, array $params = []): int;

        /**
         * Find documents/records
         *
         * @param string $collection
         * @param array $where Query parameters (key => value pair)
         * @param int|null $limit Number of rows to fetch
         * @param int $skip Number of rows to skip
         * @return \Generator Generator of results
         */
        public function find(string $collection, array $where = [], ?int $limit = null, int $skip = 0): \Generator;

        /**
         * Find documents/records
         *
         * @param string $collection
         * @param array $where Query parameters (key => value pair)
         * @param int|null $limit Number of rows to fetch
         * @param int $skip Number of rows to skip
         * @return array Rows of ReadableMap object returned as the result of the query.
         */
        public function findAll(string $collection, array $where = [], ?int $limit = null, int $skip = 0): array;

        /**
         * Execute query and returns a single row.
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param array $where Query parameters (key => value pair)
         * @return ReadableMap|null A single row returned as the result of the query
         * or null if no result found.
         * @see self::findAll
         */
        public function findOne(string $collection, array $where = []): ?ReadableMap;

        /**
         * Count matching records/documents
         * @param array $where Query parameters (key => value pair)
         */
        public function count(string $collection, array $where = []): int;

        /**
         * Check if at least one record/document exists. More efficient than count() > 0 in many cases
         * @param array $where Query parameters (key => value pair)
         */
        public function exists(string $collection, array $where = []): bool;

        /**
         * Whether to escape HTML characters on result set or not.
         * @param bool $escape When true, HTML characters will be escaped
         * @return DatabaseInterface
         */
        public function escapeHtml(bool $escape): DatabaseInterface;

        /**
         * Start a new transaction
         *
         * All subsequent CRUD operations will be part of this transaction
         * until commit() or rollback() is called.
         * @return DatabaseInterface Database object
         */
        public function beginTransaction(): DatabaseInterface;

        /**
         * Commit the current transaction
         */
        public function commit(): void;

        /**
         * Rollback the current transaction
         */
        public function rollback(): void;

        /**
         * Check if currently inside a transaction
         */
        public function inTransaction(): bool;
    }

}
