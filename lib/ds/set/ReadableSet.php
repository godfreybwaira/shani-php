<?php

/**
 * Represent iterable data set with unique values
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\ds\set {

    use lib\DataConvertor;
    use lib\ds\ReadableData;

    class ReadableSet extends ReadableData
    {

        public function exists(string|int ...$values): bool
        {
            foreach ($values as $value) {
                if (!array_key_exists($value, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        public function existsWhere(callable $callback): bool
        {
            $values = $this->toArray();
            foreach ($values as $value) {
                if ($callback($value)) {
                    return true;
                }
            }
            return false;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return $this->toArray();
        }

        #[\Override]
        public function __toString()
        {
            return implode(',', $this->toJson());
        }

        public function where(callable $callback, ?int $limit = null): array
        {
            $rows = [];
            $count = 0;
            if ($limit <= $count) {
                return $rows;
            }
            $values = $this->toArray();
            foreach ($values as $value) {
                if ($callback($value)) {
                    $rows[] = $value;
                }
                if ($limit !== null && ++$count === $limit) {
                    return $rows;
                }
            }
            return $rows;
        }

        public function toArray(): array
        {
            return array_keys($this->data);
        }

        public function toCsv(string $separator = ','): string
        {
            return DataConvertor::array2csv($this->toArray(), $separator);
        }

        public function toXml(): string
        {
            return DataConvertor::array2xml($this->toArray());
        }

        public function toJson(): string
        {
            return json_encode($this->toArray());
        }

        public function toDataGrid(): string
        {
            return DataConvertor::array2dataGrid($this->toArray());
        }

        public function reduce(callable $callback, $initialValue = null)
        {
            $accumulator = $initialValue;
            $values = $this->toArray();
            foreach ($values as $value) {
                $accumulator = $callback($value, $accumulator);
            }
            return $accumulator;
        }

        public function each(callable $callback): self
        {
            $values = $this->toArray();
            foreach ($values as $value) {
                $callback($value);
            }
            return $this;
        }
    }

}
