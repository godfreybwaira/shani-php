<?php

/**
 * Description of FilterClause
 * @author goddy
 *
 * @since Jun 5, 2026 at 3:45:48 PM
 */

namespace features\persistence {

    /**
     * FilterClause
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
    interface FilterClause extends \Stringable
    {

        /**
         * Add an equality condition (column = value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function eq(string $column, mixed $value): FilterClause;

        /**
         * Add a not-equal condition (column <> value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function neq(string $column, mixed $value): FilterClause;

        /**
         * Add a greater-than condition (column > value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function gt(string $column, mixed $value): FilterClause;

        /**
         * Add a greater-than or equal condition (column >= value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function gte(string $column, mixed $value): FilterClause;

        /**
         * Add a less-than condition (column < value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function lt(string $column, mixed $value): FilterClause;

        /**
         * Add a less-than or equal condition (column <= value).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return FilterClause Fluent interface.
         */
        public function lte(string $column, mixed $value): FilterClause;

        /**
         * Add a BETWEEN condition (column BETWEEN start AND end).
         *
         * @param string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return FilterClause Fluent interface.
         */
        public function btw(string $column, mixed $start, mixed $end): FilterClause;

        /**
         * Add a NOT BETWEEN condition (column NOT BETWEEN start AND end).
         *
         * @param string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return FilterClause Fluent interface.
         */
        public function notBtw(string $column, mixed $start, mixed $end): FilterClause;

        /**
         * Add an IN condition (column IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return FilterClause Fluent interface.
         */
        public function in(string $column, array $values): FilterClause;

        /**
         * Add an NOT IN condition (column NOT IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return FilterClause Fluent interface.
         */
        public function notIn(string $column, array $values): FilterClause;

        /**
         * Combine filters with OR.
         *
         * @param FilterClause $other Another filter instance.
         * @return FilterClause Fluent interface.
         */
        public function or(FilterClause $other): FilterClause;

        /**
         * Combine filters with AND.
         *
         * @param FilterClause $other Another filter instance.
         * @return FilterClause Fluent interface.
         */
        public function and(FilterClause $other): FilterClause;

        /**
         * Get an associative array of key-value pair from filter clauses
         * @return array
         */
        public function getValuePair(): array;

        /**
         * Get a filter type
         * @return FilterType
         */
        public function getFilterType(): FilterType;
    }

}
