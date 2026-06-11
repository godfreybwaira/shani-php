<?php

/**
 * Interface for extracting date parts (year, quarter, month, week) from a given column.
 * Provides a fluent interface for building date-based query expressions.
 *
 * @author goddy
 * @since Jun 8, 2026 at 10:46:17 AM
 */

namespace features\persistence {

    interface QueryDatePartInterface extends \Stringable
    {

        /**
         * Get the column name to extract the date part from
         * @return string Column name
         */
        public function getColumnName(): string;

        /**
         * Extract the year from a date column.
         *
         * @param string $dateColumn Date column to extract the year from.
         *
         * @return QueryDatePartInterface Fluent interface for chaining.
         */
        public static function getYear(string $dateColumn): QueryDatePartInterface;

        /**
         * Extract the quarter from a date column.
         *
         * @param string $dateColumn Date column to extract the quarter from.
         *
         * @return QueryDatePartInterface Fluent interface for chaining.
         */
        public static function getQuarter(string $dateColumn): QueryDatePartInterface;

        /**
         * Extract the month from a date column.
         *
         * @param string $dateColumn Date column to extract the month from.
         *
         * @return QueryDatePartInterface Fluent interface for chaining.
         */
        public static function getMonth(string $dateColumn): QueryDatePartInterface;

        /**
         * Extract the week from a date column.
         *
         * @param string $dateColumn Date column to extract the week from.
         *
         * @return QueryDatePartInterface Fluent interface for chaining.
         */
        public static function getWeek(string $dateColumn): QueryDatePartInterface;
    }

}
