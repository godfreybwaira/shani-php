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
    interface DBFilterInterface extends \Stringable
    {

        /**
         * Add a LIKE operator condition (column LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function like(string $column, mixed $value): DBFilterInterface;

        /**
         * Add a NOT LIKE operator condition (column NOT LIKE %value%).
         *
         * @param string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function notLike(string $column, mixed $value): DBFilterInterface;

        /**
         * Add an equality condition (column = value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function eq(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a not-equal condition (column <> value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function neq(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a greater-than condition (column > value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function gt(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a greater-than or equal condition (column >= value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function gte(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a less-than condition (column < value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function lt(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a less-than or equal condition (column <= value).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $value  Value to compare.
         * @return DBFilterInterface Fluent interface.
         */
        public function lte(DBDatePartInterface|string $column, mixed $value): DBFilterInterface;

        /**
         * Add a BETWEEN condition (column BETWEEN start AND end).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return DBFilterInterface Fluent interface.
         */
        public function btw(DBDatePartInterface|string $column, mixed $start, mixed $end): DBFilterInterface;

        /**
         * Add a NOT BETWEEN condition (column NOT BETWEEN start AND end).
         *
         * @param DBDatePartInterface|string $column Column name.
         * @param mixed  $start  Start value.
         * @param mixed  $end    End value.
         * @return DBFilterInterface Fluent interface.
         */
        public function notBtw(DBDatePartInterface|string $column, mixed $start, mixed $end): DBFilterInterface;

        /**
         * Add an IN condition (column IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return DBFilterInterface Fluent interface.
         */
        public function in(DBDatePartInterface|string $column, array $values): DBFilterInterface;

        /**
         * Add an NOT IN condition (column NOT IN (...)).
         *
         * @param string        $column Column name.
         * @param array<mixed>  $values List of values.
         * @return DBFilterInterface Fluent interface.
         */
        public function notIn(DBDatePartInterface|string $column, array $values): DBFilterInterface;

        /**
         * Combine filters with OR.
         *
         * @param DBFilterInterface $other Another filter instance.
         * @return DBFilterInterface Fluent interface.
         */
        public function or(DBFilterInterface $other): DBFilterInterface;

        /**
         * Combine filters with AND.
         *
         * @param DBFilterInterface $other Another filter instance.
         * @return DBFilterInterface Fluent interface.
         */
        public function and(DBFilterInterface $other): DBFilterInterface;

        /**
         * Get an associative array of key-value pair from filter clauses
         * @return array
         */
        public function getBindings(): array;

        /**
         * Set a filter type
         * @param DBFilterType $type Filter type
         * @return DBFilterType
         */
        public function setFilterType(DBFilterType $type): DBFilterInterface;
    }

}
