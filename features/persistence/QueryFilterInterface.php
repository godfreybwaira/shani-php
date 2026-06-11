<?php

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
    interface QueryFilterInterface extends \Stringable
    {

        /**
         * Add a LIKE operator condition (column LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function like(string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a NOT LIKE operator condition (column NOT LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function notLike(string $column, mixed $value): QueryFilterInterface;

        /**
         * Add an equality condition (column = value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function eq(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a not-equal condition (column <> value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function neq(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a greater-than condition (column > value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function gt(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a greater-than or equal condition (column >= value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function gte(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a less-than condition (column < value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function lt(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a less-than or equal condition (column <= value).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return QueryFilterInterface Fluent interface.
         */
        public function lte(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface;

        /**
         * Add a BETWEEN condition (column BETWEEN start AND end).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return QueryFilterInterface Fluent interface.
         */
        public function btw(QueryDatePartInterface|string $column, mixed $start, mixed $end): QueryFilterInterface;

        /**
         * Add a NOT BETWEEN condition (column NOT BETWEEN start AND end).
         *
         * @param QueryDatePartInterface|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return QueryFilterInterface Fluent interface.
         */
        public function notBtw(QueryDatePartInterface|string $column, mixed $start, mixed $end): QueryFilterInterface;

        /**
         * Add an IN condition (column IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return QueryFilterInterface Fluent interface.
         */
        public function in(QueryDatePartInterface|string $column, array $values): QueryFilterInterface;

        /**
         * Add an NOT IN condition (column NOT IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return QueryFilterInterface Fluent interface.
         */
        public function notIn(QueryDatePartInterface|string $column, array $values): QueryFilterInterface;

        /**
         * Combine filters with OR.
         *
         * @param QueryFilterInterface $other Another filter instance.
         * @return QueryFilterInterface Fluent interface.
         */
        public function or(QueryFilterInterface $other): QueryFilterInterface;

        /**
         * Combine filters with AND.
         *
         * @param QueryFilterInterface $other Another filter instance.
         * @return QueryFilterInterface Fluent interface.
         */
        public function and(QueryFilterInterface $other): QueryFilterInterface;

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
        public function setFilterType(QueryFilterType $type): QueryFilterInterface;
    }

}
