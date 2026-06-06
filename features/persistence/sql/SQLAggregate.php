<?php

/**
 * Description of SQLAggregate
 * @author goddy
 *
 * @since Jun 5, 2026 at 1:46:16 PM
 */

namespace features\persistence\sql {

    use features\persistence\AggregateInterface;
    use features\persistence\FilterClause;
    use features\persistence\GroupClause;

    /**
     * SQLAggregate
     *
     * Provides a fluent API for building SQL aggregate queries
     * (SUM, AVG, MIN, MAX, COUNT) with optional filtering and grouping.
     *
     * @author goddy
     * * @since  Jun 5, 2026
     */
    final class SQLAggregate implements AggregateInterface
    {

        /**
         * @var string $tableName The name of the table/collection being aggregated.
         */
        public readonly string $tableName;

        /**
         * @var SQLDatabase $db Reference to the SQLDatabase instance.
         */
        public readonly SQLDatabase $db;

        /**
         * @var array<string,self> $tables Cached instances of SQLAggregate per table.
         */
        private static $tables = [];

        /**
         * Private constructor to enforce singleton per table.
         *
         * @param SQLDatabase $db        Database connection instance.
         * @param string      $tableName Name of the table to aggregate.
         */
        private function __construct(SQLDatabase $db, string $tableName)
        {
            $this->tableName = $tableName;
            $this->db = $db;
        }

        /**
         * Get or create a SQLAggregate instance for a given table.
         *
         * @param SQLDatabase $db        Database connection instance.
         * @param string      $tableName Name of the table to aggregate.
         *
         * @return self Singleton instance bound to the specified table.
         */
        public static function getInstance(SQLDatabase $db, string $tableName): self
        {
            if (!isset(self::$tables[$tableName])) {
                self::$tables[$tableName] = new self($db, $tableName);
            }
            return self::$tables[$tableName];
        }

        /**
         * Build a WHERE clause string from parameters.
         *
         * @param array<string,mixed> $params Key-value pairs for filtering.
         *
         * @return string|null SQL WHERE clause or null if no params.
         */
        private static function getWhereClause(array $params): ?string
        {
            return SQLDatabase::createClause($params, 'WHERE', ' AND ');
        }

        /**
         * Create an AVG aggregate clause.
         *
         * @param string              $columnName Column to average.
         * @param FilterClause|null $where      Optional filters.
         *
         * @return GroupClause Aggregate clause object.
         */
        public function avgOf(string $columnName, ?FilterClause $where = null): GroupClause
        {
            return new SQLClause('AVG', $this, $columnName, $where);
        }

        /**
         * Create a MAX aggregate clause.
         *
         * @param string              $columnName Column to find maximum.
         * @param FilterClause|null $where      Optional filters.
         *
         * @return GroupClause Aggregate clause object.
         */
        public function maxOf(string $columnName, ?FilterClause $where = null): GroupClause
        {
            return new SQLClause('MAX', $this, $columnName, $where);
        }

        /**
         * Create a MIN aggregate clause.
         *
         * @param string              $columnName Column to find minimum.
         * @param FilterClause|null $where      Optional filters.
         *
         * @return GroupClause Aggregate clause object.
         */
        public function minOf(string $columnName, ?FilterClause $where = null): GroupClause
        {
            return new SQLClause('MIN', $this, $columnName, $where);
        }

        /**
         * Create a SUM aggregate clause.
         *
         * @param string              $columnName Column to sum.
         * @param FilterClause|null $where      Optional filters.
         *
         * @return GroupClause Aggregate clause object.
         */
        public function sumOf(string $columnName, ?FilterClause $where = null): GroupClause
        {
            return new SQLClause('SUM', $this, $columnName, $where);
        }

        /**
         * Create a COUNT aggregate clause.
         *
         * @param string              $columnName Column to count.
         * @param FilterClause|null $where      Optional filters.
         *
         * @return GroupClause Aggregate clause object.
         */
        public function countOf(string $columnName, ?FilterClause $where = null): GroupClause
        {
            return new SQLClause('COUNT', $this, $columnName, $where);
        }
    }

}
