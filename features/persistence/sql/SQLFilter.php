<?php

/**
 * Description of SQLFilter
 * @author goddy
 *
 * @since Jun 5, 2026 at 3:43:12 PM
 */

namespace features\persistence\sql {

    use features\persistence\DBDatePartInterface;
    use features\persistence\DBFilterInterface;
    use features\persistence\DBFilterType;

    final class SQLFilter implements DBFilterInterface
    {

        private array $clauses = [];
        private array $bindings = [];
        private readonly string $prefix;
        private DBFilterType $filter;
        private int $counter = 0;

        public function __construct()
        {
            $this->filter = DBFilterType::WHERE;
            $this->prefix = substr($this->filter->name, 0, 1);
        }

        private static function getAlias($column): string
        {
            return $column instanceof DBDatePartInterface ? $column->getColumnName() : $column;
        }

        public function like(string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[$column] = 'LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function notLike(string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[$column] = 'NOT LIKE ' . $this->bindValue($value);
            return $this;
        }

        public function eq(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '= ' . $this->bindValue($value);
            return $this;
        }

        public function neq(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '<> ' . $this->bindValue($value);
            return $this;
        }

        public function gt(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '> ' . $this->bindValue($value);
            return $this;
        }

        public function gte(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '>= ' . $this->bindValue($value);
            return $this;
        }

        public function lt(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '< ' . $this->bindValue($value);
            return $this;
        }

        public function lte(DBDatePartInterface|string $column, mixed $value): DBFilterInterface
        {
            $this->clauses[(string) $column] = '<= ' . $this->bindValue($value);
            return $this;
        }

        public function btw(DBDatePartInterface|string $column, mixed $start, mixed $end): DBFilterInterface
        {
            $this->clauses[(string) $column] = 'BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function notBtw(DBDatePartInterface|string $column, mixed $start, mixed $end): DBFilterInterface
        {
            $this->clauses[(string) $column] = 'NOT BETWEEN ' . $this->bindValue($start) . ' AND ' . $this->bindValue($end);
            return $this;
        }

        public function in(DBDatePartInterface|string $column, array $values): DBFilterInterface
        {
            $this->clauses[(string) $column] = 'IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function notIn(DBDatePartInterface|string $column, array $values): DBFilterInterface
        {
            $this->clauses[(string) $column] = 'NOT IN(' . implode(',', array_map(fn($v) => $this->bindValue($v), $values)) . ')';
            return $this;
        }

        public function or(DBFilterInterface $other): DBFilterInterface
        {
            return $this->join($other, 'OR');
        }

        public function and(DBFilterInterface $other): DBFilterInterface
        {
            return $this->join($other, 'AND');
        }

        private function join(DBFilterInterface $other, string $connector): DBFilterInterface
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

        public function setFilterType(DBFilterType $type): DBFilterInterface
        {
            $this->filter = $type;
            return $this;
        }
    }

}
