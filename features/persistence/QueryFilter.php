<?php

namespace features\persistence {

    /**
     * QueryFilter
     *
     * Provides a fluent API for building SQL WHERE clauses with support
     * for multiple operators (e.g., =, <>, >, IN, BETWEEN, AND, OR).
     * FilterInterfaces can be chained to build complex conditions and rendered
     * into SQL strings with parameter placeholders for safe binding.
     *
     * @author goddy
     *
     * @since Jun 5, 2026 at 3:45:48 PM
     */
    interface QueryFilter extends \Stringable
    {

        /**
         * Add a LIKE operator condition (column LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function like(string $column, mixed $value): QueryFilter;

        /**
         * Add a NOT LIKE operator condition (column NOT LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function notLike(string $column, mixed $value): QueryFilter;

        /**
         * Add an equality condition (column = value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function eq(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a not-equal condition (column <> value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function neq(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a greater-than condition (column > value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function gt(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a greater-than or equal condition (column >= value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function gte(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a less-than condition (column < value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function lt(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a less-than or equal condition (column <= value).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilter Fluent interface.
         */
        public function lte(QueryDatePart|string $column, mixed $value): QueryFilter;

        /**
         * Add a BETWEEN condition (column BETWEEN start AND end).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return QueryFilter Fluent interface.
         */
        public function btw(QueryDatePart|string $column, mixed $start, mixed $end): QueryFilter;

        /**
         * Add a NOT BETWEEN condition (column NOT BETWEEN start AND end).
         *
         * @param QueryDatePart|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return QueryFilter Fluent interface.
         */
        public function notBtw(QueryDatePart|string $column, mixed $start, mixed $end): QueryFilter;

        /**
         * Add an IN condition (column IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return QueryFilter Fluent interface.
         */
        public function in(QueryDatePart|string $column, array $values): QueryFilter;

        /**
         * Add an NOT IN condition (column NOT IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return QueryFilter Fluent interface.
         */
        public function notIn(QueryDatePart|string $column, array $values): QueryFilter;

        /**
         * Combine filters with OR.
         *
         * @param QueryFilter $other Another filter instance.
         * @return QueryFilter Fluent interface.
         */
        public function or(QueryFilter $other): QueryFilter;

        /**
         * Combine filters with AND.
         *
         * @param QueryFilter $other Another filter instance.
         * @return QueryFilter Fluent interface.
         */
        public function and(QueryFilter $other): QueryFilter;

        /**
         * Get an associative array of key-value pair from filter clauses
         * @return array
         */
        public function getBindings(): array;

        /**
         * Set a filter type
         * @param QueryFilterType $type Filter type
         * @return QueryFilterType
         */
        public function setFilterType(QueryFilterType $type): QueryFilter;
    }

}
