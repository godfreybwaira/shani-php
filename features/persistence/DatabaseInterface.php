<?php

/**
 * Description of DatabaseInterface
 * @author goddy
 *
 * @since Apr 18, 2026 at 8:04:10 AM
 */

namespace features\persistence {

    use features\ds\map\ReadMap;

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
         * @param \JsonSerializable $object Data to insert
         * @return string|int The inserted ID
         */
        public function insert(string $collection, \JsonSerializable $object): string|int;

        /**
         * Insert multiple documents/records. This method is transaction-aware
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param \JsonSerializable $object A @D array of data to insert
         * @return bool True if all data inserted, false otherwise
         */
        public function insertAll(string $collection, \JsonSerializable ...$object): bool;

        /**
         * Update documents/records
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param \JsonSerializable $object New data
         * @param FilterClause|null $where Query parameters (key => value pair)
         * @return int Number of modified documents
         */
        public function update(string $collection, \JsonSerializable $object, ?FilterClause $where = null): int;

        /**
         * Delete documents/records
         *
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param FilterClause $where Query parameters (key => value pair)
         * @return int Number of deleted documents
         */
        public function delete(string $collection, FilterClause $where): int;

        /**
         * Execute SQL query and fetch all rows (if available). This method is memory
         * efficient as it fetches rows on demand
         * @param string $query A query to execute
         * @param array $params Query parameters (key => value pair)
         * @return \Generator Iterable object of ReadMap contains rows returned as the result of SQL query.
         * @see self::findAll
         */
        public function query(string $query, array $params = []): \Generator;

        /**
         * Execute query and returns all rows (if available) found. For a large data set
         * use <code>query</code> for efficiency.
         * @param string $query A query to execute
         * @param array $params Query parameters (key => value pair)
         * @return array Rows of ReadMap object returned as the result of the query.
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
         * @param FilterClause|null $where Query parameters (key => value pair)
         * @param int|null $limit Number of rows to fetch
         * @param int $skip Number of rows to skip
         * @return \Generator Generator of results
         */
        public function find(string $collection, ?FilterClause $where = null, ?int $limit = null, int $skip = 0): \Generator;

        /**
         * Find documents/records
         *
         * @param string $collection
         * @param FilterClause|null $where Query parameters (key => value pair)
         * @param int|null $limit Number of rows to fetch
         * @param int $skip Number of rows to skip
         * @return array Rows of ReadMap object returned as the result of the query.
         */
        public function findAll(string $collection, ?FilterClause $where = null, ?int $limit = null, int $skip = 0): array;

        /**
         * Execute query and returns a single row.
         * @param string $collection Table name (SQL) or Collection name (NoSQL)
         * @param FilterClause|null $where Query parameters (key => value pair)
         * @return ReadMap|null A single row returned as the result of the query
         * or null if no result found.
         * @see self::findAll
         */
        public function findOne(string $collection, ?FilterClause $where = null): ?ReadMap;

        /**
         * Check if at least one record/document exists. More efficient than count() > 0 in many cases
         * @param FilterClause|null $where Query parameters (key => value pair)
         */
        public function exists(string $collection, ?FilterClause $where = null): bool;

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
         * @return bool True if transaction started successfully, false otherwise
         */
        public function beginTransaction(): bool;

        /**
         * Ends the current database transaction based on a given condition.
         *
         * If a transaction is active:
         * - Commits the transaction when $condition is true.
         * - Rolls back the transaction when $condition is false.
         *
         * @param bool $condition Determines whether to commit (true) or rollback (false).
         * @return bool Returns the same condition value passed in, indicating the outcome.
         */
        public function endTransaction(bool $condition): bool;

        /**
         * Commit the current transaction
         */
        public function commit(): void;

        /**
         * Rollback the current transaction
         */
        public function rollback(): void;

        /**
         * Create a new aggregate query builder for the given collection.
         *
         * This method initializes an AggregateInterface instance bound to the
         * specified collection. It allows you to build aggregate queries with
         * metrics, filters, grouping, rollups, and ordering in a fluent style.
         *
         * @param string $collection The name of the collection (e.g., table) to aggregate.
         *
         * @return AggregateInterface Returns an aggregate query builder instance
         *                            for chaining aggregate operations.
         */
        public function aggregate(string $collection): AggregateInterface;
    }

}
