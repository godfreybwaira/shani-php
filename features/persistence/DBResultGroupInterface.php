<?php

namespace features\persistence {

    /**
     * DBResultGroupInterface
     *
     * Defines the contract for SQL aggregate clauses that support
     * grouping and execution. Implementations should provide a fluent
     * API for building SQL queries with GROUP BY and ORDER BY semantics,
     * and a method to run the query against the database.
     *
     * @author goddy
     *
     * @since Jun 5, 2026 at 1:36:20 PM
     */
    interface DBResultGroupInterface extends \Stringable
    {

        /**
         * Add a WHERE clause.
         *
         * @param DBFilterInterface $where  Where clause object.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function whereBy(DBFilterInterface $where): DBResultGroupInterface;

        /**
         * Add a GROUP BY clause with optional ordering.
         *
         * @param string    $columnName Column to group by.
         * @param bool|null $sortAsc  True for sorting ASC, false for DESC, null for no ordering.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function groupBy(string $columnName, ?bool $sortAsc = null): DBResultGroupInterface;

        /**
         * Add a GROUP BY clause grouping by quarter from a date column.
         *
         * @param string    $dateColumn Date column to group by quarter.
         * @param string    $displayName Column alias (display name).
         * @param bool|null $sortAsc  True for sorting ASC, false for DESC, null for no ordering.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function groupByQuarter(string $dateColumn, string $displayName = null, ?bool $sortAsc = null): DBResultGroupInterface;

        /**
         * Add a GROUP BY clause grouping by month from a date column.
         *
         * @param string    $dateColumn Date column to group by month.
         * @param string    $displayName Column alias (display name)
         * @param bool|null $sortAsc  True for sorting ASC, false for DESC, null for no ordering.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function groupByMonth(string $dateColumn, string $displayName = null, ?bool $sortAsc = null): DBResultGroupInterface;

        /**
         * Add a GROUP BY clause grouping by year from a date column.
         *
         * @param string    $dateColumn Date column to group by year.
         * @param string    $displayName Column alias (display name)
         * @param bool|null $sortAsc  True for sorting ASC, false for DESC, null for no ordering.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function groupByYear(string $dateColumn, string $displayName = null, ?bool $sortAsc = null): DBResultGroupInterface;

        /**
         * Add a GROUP BY clause grouping by week from a date column.
         *
         * @param string    $dateColumn Date column to group by week.
         * @param string    $displayName Column alias (display name)
         * @param bool|null ascending   True for sorting ASC, false for DESC, null for no ordering.
         *
         * @return DBResultGroupInterface Fluent interface for chaining.
         */
        public function groupByWeek(string $dateColumn, string $displayName = null, ?bool $sortAsc = null): DBResultGroupInterface;

        /**
         * Adds a HAVING clause to the current group clause.
         *
         * This method ensures that the provided FilterClause only references
         * columns that are part of the GROUP BY clause. If the group-by clause
         * is empty, or if the FilterClause contains columns not present in the
         * group-by, a RuntimeException is thrown.
         *
         * @param DBFilterInterface $having The filter clause to apply in the HAVING context.
         *
         * @throws \RuntimeException If the group-by clause is empty or if the
         * FilterClause contains columns not in the group-by.
         *
         * @return DBResultGroupInterface Returns the updated GroupClause instance with the
         * HAVING condition applied.
         */
        public function having(DBFilterInterface $having): DBResultGroupInterface;

        /**
         * Execute the aggregate query and return results.
         *
         * @return array<int,array<string,mixed>> Result set as associative arrays.
         */
        public function run(): array;
    }

}
