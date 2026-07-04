<?php

/**
 * Description of SQLFilter
 * @author goddy
 *
 * @since Jun 5, 2026 at 3:43:12 PM
 */

namespace features\persistence\sql {

    use features\persistence\QueryDatePart;
    use features\persistence\QueryFilter;
    use features\persistence\QueryFilterType;

    final class SQLFilter implements QueryFilter
    {

        private array $clauses = [];
        private array $bindings = [];
        private readonly string $prefix;
        private QueryFilterType $filter;
        private int $counter = 0;

        public function __construct()
        {
            $this->filter = QueryFilterType::WHERE;
            $this->prefix = substr($this->filter->name, 0, 1);
        }

        private static function getAlias($column): string
        {
            return $column instanceof QueryDatePart ? $column->getColumnName() : $column;
        }

        public function like(string $column, mixed $value): QueryFilter
        {
            $this->clauses[$column] = 'LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function notLike(string $column, mixed $value): QueryFilter
        {
            $this->clauses[$column] = 'NOT LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function eq(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '= ' . $this->bindValue($value);
            return $this;
        }

        public function neq(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '<> ' . $this->bindValue($value);
            return $this;
        }

        public function gt(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '> ' . $this->bindValue($value);
            return $this;
        }

        public function gte(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '>= ' . $this->bindValue($value);
            return $this;
        }

        public function lt(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '< ' . $this->bindValue($value);
            return $this;
        }

        public function lte(QueryDatePart|string $column, mixed $value): QueryFilter
        {
            $this->clauses[(string) $column] = '<= ' . $this->bindValue($value);
            return $this;
        }

        public function btw(QueryDatePart|string $column, mixed $start, mixed $end): QueryFilter
        {
            $this->clauses[(string) $column] = 'BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function notBtw(QueryDatePart|string $column, mixed $start, mixed $end): QueryFilter
        {
            $this->clauses[(string) $column] = 'NOT BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function in(QueryDatePart|string $column, array $values): QueryFilter
        {
            $this->clauses[(string) $column] = 'IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function notIn(QueryDatePart|string $column, array $values): QueryFilter
        {
            $this->clauses[(string) $column] = 'NOT IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function or(QueryFilter $other): QueryFilter
        {
            return $this->join($other, 'OR');
        }

        public function and(QueryFilter $other): QueryFilter
        {
            return $this->join($other, 'AND');
        }

        private function join(QueryFilter $other, string $connector): QueryFilter
        {
            $filter = new self();
            if (!empty($this->clauses)) {
                $thisSQL = '(' . $this->asString() . ')';
                $otherSQL = '(' . $other->asString() . ')';
                $filter->clauses = [$thisSQL . " $connector " . $otherSQL];
                $filter->bindings = array_merge($this->bindings, $other->getBindings());
            }
            return $filter;
        }

        private function bindValue(mixed $value): string
        {
            $param = $this->prefix . ($this->counter++);
            $this->bindings[$param] = $value;
            return ':' . $param;
        }

        private function asString(): string
        {
            $parts = [];
            foreach ($this->clauses as $column => $value) {
                if (is_int($column)) {
                    $parts[] = $value;
                } else {
                    $parts[] = $column . ' ' . $value;
                }
            }
            return implode(' AND ', $parts);
        }

        public function __toString(): string
        {
            return ' ' . $this->filter->name . ' ' . $this->asString();
        }

        public function getBindings(): array
        {
            return $this->bindings;
        }

        public function setFilterType(QueryFilterType $type): QueryFilter
        {
            $this->filter = $type;
            return $this;
        }
    }

}
