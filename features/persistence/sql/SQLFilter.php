<?php

/**
 * Description of SQLFilter
 * @author goddy
 *
 * @since Jun 5, 2026 at 3:43:12 PM
 */

namespace features\persistence\sql {

    use features\persistence\QueryDatePartInterface;
    use features\persistence\QueryFilterInterface;
    use features\persistence\QueryFilterType;

    final class SQLFilter implements QueryFilterInterface
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
            return $column instanceof QueryDatePartInterface ? $column->getColumnName() : $column;
        }

        public function like(string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[$column] = 'LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function notLike(string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[$column] = 'NOT LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function eq(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '= ' . $this->bindValue($value);
            return $this;
        }

        public function neq(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '<> ' . $this->bindValue($value);
            return $this;
        }

        public function gt(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '> ' . $this->bindValue($value);
            return $this;
        }

        public function gte(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '>= ' . $this->bindValue($value);
            return $this;
        }

        public function lt(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '< ' . $this->bindValue($value);
            return $this;
        }

        public function lte(QueryDatePartInterface|string $column, mixed $value): QueryFilterInterface
        {
            $this->clauses[(string) $column] = '<= ' . $this->bindValue($value);
            return $this;
        }

        public function btw(QueryDatePartInterface|string $column, mixed $start, mixed $end): QueryFilterInterface
        {
            $this->clauses[(string) $column] = 'BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function notBtw(QueryDatePartInterface|string $column, mixed $start, mixed $end): QueryFilterInterface
        {
            $this->clauses[(string) $column] = 'NOT BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function in(QueryDatePartInterface|string $column, array $values): QueryFilterInterface
        {
            $this->clauses[(string) $column] = 'IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function notIn(QueryDatePartInterface|string $column, array $values): QueryFilterInterface
        {
            $this->clauses[(string) $column] = 'NOT IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function or(QueryFilterInterface $other): QueryFilterInterface
        {
            return $this->join($other, 'OR');
        }

        public function and(QueryFilterInterface $other): QueryFilterInterface
        {
            return $this->join($other, 'AND');
        }

        private function join(QueryFilterInterface $other, string $connector): QueryFilterInterface
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

        public function setFilterType(QueryFilterType $type): QueryFilterInterface
        {
            $this->filter = $type;
            return $this;
        }
    }

}
