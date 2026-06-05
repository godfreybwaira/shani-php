<?php

/**
 * Enum representing different SQL filter clause types.
 *
 * This enum is used to distinguish whether a filter applies
 * to the WHERE clause or the HAVING clause in SQL queries.
 *
 * @author goddy
 * @since v1.0: Jun 5, 2026 at 10:47:15 PM
 */

namespace features\persistence {

    /**
     * FilterType enum
     *
     * Provides constants for SQL filter clause contexts:
     * - WHERE: Used for filtering rows before grouping.
     * - HAVING: Used for filtering groups after aggregation.
     */
    enum FilterType
    {

        /**
         * Represents a SQL WHERE clause filter.
         *
         * Typically used to restrict rows before
         * any grouping or aggregation occurs.
         */
        case WHERE;

        /**
         * Represents a SQL HAVING clause filter.
         *
         * Typically used to restrict groups after
         * aggregation functions have been applied.
         */
        case HAVING;
    }

}
