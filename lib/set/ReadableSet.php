<?php

/**
 * Represent iterable data set with unique values
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\set {

    use lib\DataConvertor;

    class ReadableSet implements \Stringable, \JsonSerializable
    {

        protected array $data;

        public function __construct(array $data = [])
        {
            $this->data = $data;
        }

        /**
         * Check if an iterable object has given item
         * @param string|int $values Items to check
         * @return bool
         */
        public function exists(string|int ...$values): bool
        {
            foreach ($values as $value) {
                if (!array_key_exists($value, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Check if all items mentioned exist in the current iterable object
         * @param callable $callback List of item names
         * @return bool Returns true if all items exists, false otherwise.
         */
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

        /**
         * Count number of items in an iterable object
         * @return int
         */
        public function count(): int
        {
            return count($this->data);
        }

        /**
         * Check if an iterable object is empty or not
         * @return bool
         */
        public function isEmpty(): bool
        {
            return empty($this->data);
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

        /**
         * Get all items which satisfies the condition provided by the callback
         * function.
         * @param callable $callback A callback function that receive an item.
         * This function must return a boolean value.
         * @param int|null $limit When to stop finding
         * @return array A list if items
         */
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

        /**
         * Get an iterable object as array
         * @param array $keys Items to get from an iterable object
         * @return array
         */
        public function toArray(): array
        {
            return array_keys($this->data);
        }

        /**
         * Convert iterable data to CSV
         * @param string $separator Data separator
         * @return string
         */
        public function toCsv(string $separator = ','): string
        {
            return DataConvertor::array2csv($this->toArray(), $separator);
        }

        /**
         * Convert iterable data to XML
         * @return string
         */
        public function toXml(): string
        {
            return DataConvertor::array2xml($this->toArray());
        }

        /**
         * Convert iterable data to JSON
         * @return string
         */
        public function toJson(): string
        {
            return json_encode($this->toArray());
        }

        /**
         * Convert iterable data to datagrid, A json with first row as headers,
         * and second row contains a list of values corresponding to given headers
         * @return string
         */
        public function toDataGrid(): string
        {
            return DataConvertor::array2dataGrid($this->toArray());
        }

        /**
         * Reduce an array to a scalar value, for example when finding sum or
         * average of an array column
         * @param callable $callback a callback that accepts two arguments,
         * an array value and accumulator. The type of accumulator
         * is same as that of <code>$initialValue</code>
         * @param type $initialValue An initial accumulator value
         * @return type A single scalar value
         */
        public function reduce(callable $callback, $initialValue = null)
        {
            $accumulator = $initialValue;
            $values = $this->toArray();
            foreach ($values as $value) {
                $accumulator = $callback($value, $accumulator);
            }
            return $accumulator;
        }

        /**
         * Apply a callback function for each value of a collection i.e array.
         * @param callable $callback A callback function that receives an item value.
         * @return self
         */
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
