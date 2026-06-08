<?php

namespace features\persistence {

    /**
     * DBFilterType enum
     *
     * Provides constants for SQL filter clause contexts:
     * - WHERE: Used for filtering rows before grouping.
     * - HAVING: Used for filtering groups after aggregation.
     *
     * @author goddy
     * @since v1.0: Jun 5, 2026 at 10:47:15 PM
     */
    enum DBFilterType
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
