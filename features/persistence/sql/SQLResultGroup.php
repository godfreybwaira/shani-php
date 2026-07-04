<?php

/**
 * Description of SQLPredicate
 * @author goddy
 *
 * @since Jun 5, 2026 at 1:55:41 PM
 */

namespace features\persistence\sql {

    use features\persistence\QueryFilter;
    use features\persistence\QueryFilterType;
    use features\persistence\ResultGroup;

    /**
     * SQLResultGroup
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
    final class SQLResultGroup implements ResultGroup
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
         * @var QueryFilter|null $where Filter conditions for the clause.
         */
        private ?QueryFilter $where = null;

        /**
         * @var QueryFilter|null $having Filter conditions for the clause.
         */
        private ?QueryFilter $having = null;

        /**
         * @var array<string> $groups Grouping definitions.
         */
        private array $groups = [];

        /**
         * @var array<string> $orders Ordering definitions.
         */
        private array $orders = [];

        /**
         * Construct a new SQLClause.
         *
         * @param string              $aggFn      Aggregate function name (SUM, AVG, MIN, MAX, COUNT).
         * @param SQLAggregate        $aggregate  Parent aggregate instance.
         * @param string              $columnName Column to aggregate.
         * @param string|null $displayName      Optional displayName.
         */
        public function __construct(string $aggFn, SQLAggregate $aggregate, string $columnName, ?string $displayName)
        {
            $this->aggClause = $aggFn . '(' . ($aggFn === 'COUNT' ? '*' : $columnName) . ') AS ' . ($displayName ?? $columnName);
            $this->aggregate = $aggregate;
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

        public function filterBy(QueryFilter $where): ResultGroup
        {
            $this->where = $where;
            return $this;
        }

        public function groupBy(string $columnName, ?bool $sortAsc = null): ResultGroup
        {
            return $this->addGroupBy($columnName, null, $sortAsc);
        }

        public function groupByYear(string $columnName, string $displayName = null, ?bool $ascending = null): ResultGroup
        {
            $column = SQLDatePart::getYear($columnName);
            return $this->addGroupBy($column, $displayName ?? $column->getColumnName(), $ascending);
        }

        public function groupByQuarter(string $columnName, string $displayName = null, ?bool $ascending = null): ResultGroup
        {
            $column = SQLDatePart::getQuarter($columnName);
            return $this->addGroupBy($column, $displayName ?? $column->getColumnName(), $ascending);
        }

        public function groupByMonth(string $columnName, string $displayName = null, ?bool $ascending = null): ResultGroup
        {
            $column = SQLDatePart::getMonth($columnName);
            return $this->addGroupBy($column, $displayName ?? $column->getColumnName(), $ascending);
        }

        public function groupByWeek(string $columnName, string $displayName = null, ?bool $ascending = null): ResultGroup
        {
            $column = SQLDatePart::getWeek($columnName);
            return $this->addGroupBy($column, $displayName ?? $column->getColumnName(), $ascending);
        }

        private function addGroupBy(string $columnName, ?string $alias, ?bool $ascending): ResultGroup
        {
            if (!array_key_exists($columnName, $this->groups)) {
                $this->groups[$columnName] = $alias;
                if ($ascending !== null) {
                    $this->orders[$columnName] = $ascending ? 'ASC' : 'DESC';
                }
            }
            return $this;
        }

        public function having(QueryFilter $having): ResultGroup
        {
            if (empty($this->groups)) {
                throw new \RuntimeException('Group-by clause is empty.');
            }
            $values = array_keys($having->getBindings());
            foreach ($values as $column) {
                if (!array_key_exists($column, $this->groups)) {
                    throw new \RuntimeException('Column "' . $column . '" is not in group-by clause.');
                }
            }
            $having->setFilterType(QueryFilterType::HAVING);
            $this->having = $having;
            return $this;
        }

        /**
         * Execute the aggregate query and return results.
         *
         * @return array<int,ReadMap> Result set.
         */
        public function run(): array
        {
            $whereParams = $this->where?->getBindings() ?? [];
            $havingParams = $this->having?->getBindings() ?? [];
            $data = $this->aggregate->db->query($this, array_merge($whereParams, $havingParams));
            $resultSet = [];
            foreach ($data as $value) {
                $resultSet[] = $value;
            }
            return $resultSet;
        }

        /**
         * Render the clause as a SQL string.
         *
         * @return string SQL query string.
         */
        public function __toString(): string
        {
            $groupList = [$this->aggClause];
            foreach ($this->groups as $column => $alias) {
                $groupList[] = $column . ($alias !== null ? ' AS ' . $alias : null);
            }
            $sql = 'SELECT ' . implode(', ', $groupList) . ' FROM ' . $this->aggregate->tableName;
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
            if (empty($this->orders)) {
                return null;
            }
            $clause = [];
            foreach ($this->orders as $column => $order) {
                $clause[] = $column . ' ' . $order;
            }
            return ' ORDER BY ' . implode(', ', $clause);
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
