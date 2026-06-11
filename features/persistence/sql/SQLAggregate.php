<?php

/**
 * Description of SQLAggregate
 * @author goddy
 *
 * @since Jun 5, 2026 at 1:46:16 PM
 */

namespace features\persistence\sql {

    use features\persistence\QueryAggregateInterface;
    use features\persistence\QueryFilterInterface;
    use features\persistence\ResultGroupInterface;

    /**
     * SQLAggregate
     *
     * Provides a fluent API for building SQL aggregate queries
     * (SUM, AVG, MIN, MAX, COUNT) with optional filtering and grouping.
     *
     * @author goddy
     * * @since  Jun 5, 2026
     */
    final class SQLAggregate implements QueryAggregateInterface
    {

        /**
         * @var string $tableName The name of the table/collection being aggregated.
         */
        public readonly string $tableName;

        /**
         * @var SQLQuery $db Reference to the SQLDatabase instance.
         */
        public readonly SQLQuery $db;

        /**
         * @var array<string,self> $tables Cached instances of SQLAggregate per table.
         */
        private static $tables = [];

        /**
         * Private constructor to enforce singleton per table.
         *
         * @param SQLQuery $db        Database connection instance.
         * @param string      $tableName Name of the table to aggregate.
         */
        private function __construct(SQLQuery $db, string $tableName)
        {
            $this->tableName = $tableName;
            $this->db = $db;
        }

        /**
         * Get or create a SQLAggregate instance for a given table.
         *
         * @param SQLQuery $db        Database connection instance.
         * @param string      $tableName Name of the table to aggregate.
         *
         * @return self Singleton instance bound to the specified table.
         */
        public static function getInstance(SQLQuery $db, string $tableName): self
        {
            if (!isset(self::$tables[$tableName])) {
                self::$tables[$tableName] = new self($db, $tableName);
            }
            return self::$tables[$tableName];
        }

        public function avgOf(string $columnName, string $displayName = null): ResultGroupInterface
        {
            return new SQLResultGroup('AVG', $this, $columnName, $displayName);
        }

        public function maxOf(string $columnName, string $displayName = null): ResultGroupInterface
        {
            return new SQLResultGroup('MAX', $this, $columnName, $displayName);
        }

        public function minOf(string $columnName, string $displayName = null): ResultGroupInterface
        {
            return new SQLResultGroup('MIN', $this, $columnName, $displayName);
        }

        public function sumOf(string $columnName, string $displayName = null): ResultGroupInterface
        {
            return new SQLResultGroup('SUM', $this, $columnName, $displayName);
        }

        public function countOf(string $columnName, string $displayName = null): ResultGroupInterface
        {
            return new SQLResultGroup('COUNT', $this, $columnName, $displayName);
        }
    }

}
