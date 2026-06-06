<?php

/**
 * Description of SQLPredicate
 * @author goddy
 *
 * @since Jun 5, 2026 at 1:55:41 PM
 */

namespace features\persistence\sql {

    use features\persistence\FilterClause;
    use features\persistence\FilterType;
    use features\persistence\GroupClause;

    /**
     * SQLClause
     *
     * Represents a single SQL aggregate clause (SUM, AVG, MIN, MAX, COUNT)
     * with optional filtering, grouping, and ordering. Provides a fluent API
     * for building aggregate queries and rendering them as SQL strings.
     *
     * Example usage:
     * $db->aggregate('sales')
     *     ->sumOf('sales_amount', ['region' => 'mwanza', 'paid' => true])
     *     ->groupBy('customer_id', true)
     *     ->groupBy('store_id', false)
     *     ->run();
     *
     * This will generate SQL similar to:
     * SELECT customer_id, store_id, SUM(sales_amount) AS sales_amount
     * FROM sales
     * WHERE region = :region AND paid = :paid
     * GROUP BY customer_id, store_id
     * ORDER BY customer_id ASC, store_id DESC;
     *
     * @author goddy
     * * @since  Jun 5, 2026
     */
    final class SQLClause implements GroupClause
    {

        /**
         * @var string $aggClause The aggregate function clause (e.g., SUM(column) AS alias).
         */
        private readonly string $aggClause;

        /**
         * @var SQLAggregate $aggregate The parent SQLAggregate instance.
         */
        private readonly SQLAggregate $aggregate;

        /**
         * @var FilterClause|null $where Filter conditions for the clause.
         */
        private readonly ?FilterClause $where;

        /**
         * @var FilterClause|null $having Filter conditions for the clause.
         */
        private ?FilterClause $having = null;

        /**
         * @var array<string,string|null> $groups Grouping and ordering definitions.
         */
        private array $groups = [];

        /**
         * Construct a new SQLClause.
         *
         * @param string              $aggFn      Aggregate function name (SUM, AVG, MIN, MAX, COUNT).
         * @param SQLAggregate        $aggregate  Parent aggregate instance.
         * @param string              $columnName Column to aggregate.
         * @param FilterClause|null $where      Optional filters.
         */
        public function __construct(string $aggFn, SQLAggregate $aggregate, string $columnName, ?FilterClause $where)
        {
            $this->aggClause = $aggFn . '(' . ($aggFn === 'COUNT' ? '*' : $columnName) . ') AS ' . $columnName;
            $this->aggregate = $aggregate;
            $this->where = $where;
        }

        /**
         * Create a generic SQL clause string from parameters.
         *
         * @param array|null<string,mixed> $params Key-value filter pairs.
         * @param string              $clause SQL clause keyword (e.g., WHERE).
         * @param string              $join   Join operator (e.g., AND).
         * @param string|null         $prefix Optional parameter prefix.
         *
         * @return string|null SQL clause string or null if no params.
         */
        public static function createClause(array|null $params, string $clause, string $join, ?string $prefix = null): ?string
        {
            if (empty($params)) {
                return null;
            }
            $filters = [];
            foreach ($params as $key => $value) {
                $filters[] = $key . '=:' . $prefix . $key;
            }
            return !empty($filters) ? " $clause " . implode($join, $filters) : null;
        }

        /**
         * Add a GROUP BY clause with optional ordering.
         *
         * @param string   $columnName Column to group by.
         * @param bool|null $ascending  True for ASC, false for DESC, null for no ordering.
         *
         * @return GroupClause Fluent interface for chaining.
         */
        public function groupBy(string $columnName, ?bool $ascending = null): GroupClause
        {
            $this->groups[$columnName] = $ascending ? 'ASC' : ($ascending === false ? 'DESC' : null);
            return $this;
        }

        public function having(FilterClause $having): GroupClause
        {
            if (empty($this->groups)) {
                throw new \RuntimeException('Group-by clause is empty.');
            }
            $having->setFilterType(FilterType::HAVING);
            $values = array_keys($having->getValuePair());
            foreach ($values as $column) {
                if (!array_key_exists($column, $this->groups)) {
                    throw new \RuntimeException('Column "' . $column . '" is not in group-by clause.');
                }
            }
            $this->having = $having;
            return $this;
        }

        /**
         * Execute the aggregate query and return results.
         *
         * @return array<int,array<string,mixed>> Result set as associative arrays.
         */
        public function run(): array
        {
            $whereParams = $this->where?->getValuePair() ?? [];
            $havingParams = $this->having?->getValuePair() ?? [];
            return $this->aggregate->db->queryAll($this, array_merge($whereParams, $havingParams));
        }

        /**
         * Render the clause as a SQL string.
         *
         * @return string SQL query string.
         */
        public function __toString(): string
        {
            $columns = array_merge(array_keys($this->groups), [$this->aggClause]);
            $sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $this->aggregate->tableName;
            return $sql . $this->where . $this->getGroupClause() . $this->having . $this->getOrderClause();
        }

        // --- Private helpers ---

        /**
         * Build the ORDER BY clause.
         *
         * @return string|null SQL ORDER BY clause or null.
         */
        private function getOrderClause(): ?string
        {
            $clause = [];
            foreach ($this->groups as $column => $value) {
                if ($value !== null) {
                    $clause[] = $column . ' ' . $value;
                }
            }
            return !empty($clause) ? ' ORDER BY ' . implode(', ', $clause) : null;
        }

        /**
         * Build the GROUP BY clause.
         *
         * @return string|null SQL GROUP BY clause or null.
         */
        private function getGroupClause(): ?string
        {
            return !empty($this->groups) ? ' GROUP BY ' . implode(', ', array_keys($this->groups)) : null;
        }
    }

}
