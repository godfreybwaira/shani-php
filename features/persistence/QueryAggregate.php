<?php

/**
 * Interface for defining aggregate query operations such as SUM, AVG, MAX, MIN, and COUNT.
 * Provides a fluent interface for building aggregate queries with optional filtering.
 *
 * @author goddy
 * @since Jun 5, 2026 at 1:33:25 PM
 */

namespace features\persistence {

    interface QueryAggregate
    {

        /**
         * Calculate the SUM of values in a column, optionally filtered.
         *
         * @param string                 $columnName Column to aggregate.
         * @param string $displayName Optional display name.
         *
         * @return ResultGroup Fluent interface for chaining.
         *
         * @example
         * SELECT SUM(total) AS sum_total
         * FROM sales
         * WHERE region = 'East';
         */
        public function sumOf(string $columnName, string $displayName = null): ResultGroup;

        /**
         * Calculate the AVG (average) of values in a column, optionally filtered.
         *
         * @param string                 $columnName Column to aggregate.
         * @param string $displayName Optional display name.
         *
         * @return ResultGroup Fluent interface for chaining.
         *
         * @example
         * SELECT AVG(price) AS avg_price
         * FROM products
         * WHERE category = 'Electronics';
         */
        public function avgOf(string $columnName, string $displayName = null): ResultGroup;

        /**
         * Calculate the MAX (maximum) value in a column, optionally filtered.
         *
         * @param string                 $columnName Column to aggregate.
         * @param string $displayName Optional display name.
         *
         * @return ResultGroup Fluent interface for chaining.
         *
         * @example
         * SELECT MAX(salary) AS max_salary
         * FROM employees
         * WHERE department = 'HR';
         */
        public function maxOf(string $columnName, string $displayName = null): ResultGroup;

        /**
         * Calculate the MIN (minimum) value in a column, optionally filtered.
         *
         * @param string                 $columnName Column to aggregate.
         * @param string $displayName Optional display name.
         *
         * @return ResultGroup Fluent interface for chaining.
         *
         * @example
         * SELECT MIN(order_date) AS min_order
         * FROM orders
         * WHERE customer_id = 123;
         */
        public function minOf(string $columnName, string $displayName = null): ResultGroup;

        /**
         * Calculate the COUNT of rows or values in a column, optionally filtered.
         *
         * @param string                 $columnName Column to count.
         * @param string $displayName Optional display name.
         *
         * @return ResultGroup Fluent interface for chaining.
         *
         * @example
         * SELECT COUNT(order_id) AS count_order
         * FROM orders
         * WHERE status = 'Completed';
         */
        public function countOf(string $columnName, string $displayName = null): ResultGroup;
    }

}
