<?php

/**
 * Description of SQLFilter
 * @author goddy
 *
 * @since Jun 5, 2026 at 3:43:12 PM
 */

namespace features\persistence\sql {

    use features\persistence\FilterClause;
    use features\persistence\FilterType;

    final class SQLFilter implements FilterClause
    {

        private array $conditions = [];
        private array $valuePairs = [];
        private readonly string $suffix;
        private FilterType $filter;

        public function __construct()
        {
            $this->filter = FilterType::WHERE;
            $this->suffix = substr($this->filter->name, 0, 1);
        }

        public function like(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['LIKE', $value];
            return $this;
        }

        public function notLike(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['NOT LIKE', $value];
            return $this;
        }

        public function eq(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['=', $value];
            return $this;
        }

        public function neq(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['<>', $value];
            return $this;
        }

        public function gt(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['>', $value];
            return $this;
        }

        public function gte(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['>=', $value];
            return $this;
        }

        public function lt(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['<', $value];
            return $this;
        }

        public function lte(string $column, mixed $value): FilterClause
        {
            $this->valuePairs[$column] = ['<=', $value];
            return $this;
        }

        public function btw(string $column, mixed $start, mixed $end): FilterClause
        {
            $this->valuePairs[$column] = ['BETWEEN', [$start, $end]];
            return $this;
        }

        public function notBtw(string $column, mixed $start, mixed $end): FilterClause
        {
            $this->valuePairs[$column] = ['NOT BETWEEN', [$start, $end]];
            return $this;
        }

        public function in(string $column, array $values): FilterClause
        {
            $this->valuePairs[$column] = ['IN', $values];
            return $this;
        }

        public function notIn(string $column, array $values): FilterClause
        {
            $this->valuePairs[$column] = ['NOT IN', $values];
            return $this;
        }

        public function or(FilterClause $other): FilterClause
        {
            $this->conditions[] = ['OR', $other];
            return $this;
        }

        public function and(FilterClause $other): FilterClause
        {
            $this->conditions[] = ['AND', $other];
            return $this;
        }

        private function asString(): string
        {
            $parts = [];
            foreach ($this->valuePairs as $column => [$operator, $value]) {
                if ($operator === 'IN' || $operator === 'NOT IN') {
                    $placeholders = implode(',', array_map(fn($k) => ":{$column}_$k{$this->suffix}", array_keys($value)));
                    $parts[] = "$column $operator($placeholders)";
                } elseif ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
                    $parts[] = "$column $operator :{$column}_0{$this->suffix} AND :{$column}_1{$this->suffix}";
                } else {
                    $parts[] = "$column $operator :$column";
                }
            }
            $str = implode(' AND ', $parts);
            foreach ($this->conditions as [$operator, $filter]) {
                $str = "($str) $operator (" . $filter->asString() . ')';
            }
            return $str;
        }

        public function __toString(): string
        {
            return ' ' . $this->filter->name . ' ' . $this->asString();
        }

        public function getValuePair(): array
        {
            $pairs = [];
            foreach ($this->valuePairs as $column => [$operator, $value]) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $pairs["{$column}_$k{$this->suffix}"] = $v;
                    }
                } elseif ($operator === 'LIKE' || $operator === 'NOT LIKE') {
                    $pairs[$column] = "%{$value}%";
                } else {
                    $pairs[$column] = $value;
                }
            }
            return $pairs;
        }

        public function setFilterType(FilterType $type): FilterClause
        {
            $this->filter = $type;
            return $this;
        }
    }

}
