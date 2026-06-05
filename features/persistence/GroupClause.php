<?php

namespace features\persistence {

    /**
     * GroupClause
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
    interface GroupClause extends \Stringable
    {

        /**
         * Add a GROUP BY clause with optional ordering.
         *
         * @param string $columnName Column to group by.
         * @param bool   $ascending  True for ASC, false for DESC.
         *
         * @return GroupClause Fluent interface for chaining.
         */
        public function groupBy(string $columnName, bool $ascending = true): GroupClause;

        /**
         * Adds a HAVING clause to the current group clause.
         *
         * This method ensures that the provided FilterClause only
         * references columns that are part of the GROUP BY clause.
         * If the group-by clause is empty, or if the FilterClause
         * contains columns not present in the group-by, a RuntimeException
         * is thrown.
         *
         * @param FilterClause $having The filter clause to apply in the HAVING context.
         *
         * @throws \RuntimeException If the group-by clause is empty or if the
         *                           FilterClause contains columns not in the group-by.
         *
         * @return GroupClause Returns the updated GroupClause instance with the
         *                     HAVING condition applied.
         */
        public function having(FilterClause $having): GroupClause;

        /**
         * Execute the aggregate query and return results.
         *
         * @return array<int,array<string,mixed>> Result set as associative arrays.
         */
        public function run(): array;
    }

}
