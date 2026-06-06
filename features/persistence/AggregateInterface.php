<?php

/**
 * Description of AggregateInterface
 * @author goddy
 *
 * @since Jun 5, 2026 at 1:33:25 PM
 */

namespace features\persistence {

    interface AggregateInterface
    {

        public function sumOf(string $columnName, ?FilterClause $where = null): GroupClause;

        public function avgOf(string $columnName, ?FilterClause $where = null): GroupClause;

        public function maxOf(string $columnName, ?FilterClause $where = null): GroupClause;

        public function minOf(string $columnName, ?FilterClause $where = null): GroupClause;

        public function countOf(string $columnName, ?FilterClause $where = null): GroupClause;
    }

}
