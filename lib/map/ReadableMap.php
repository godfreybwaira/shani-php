<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\map {

    use lib\DataConvertor;

    class ReadableMap implements \Stringable, \JsonSerializable
    {

        protected array $data;

        public function __construct(array $data = [])
        {
            $this->data = $data;
        }

        /**
         * Check if an iterable object has given item
         * @param string|int $key Items to check
         * @return bool
         */
        public function exists(string|int ...$key): bool
        {
            foreach ($key as $key) {
                if (!array_key_exists($key, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Check if all carts mentioned exist in the current session object
         * @param callable $callback List of cart names
         * @return bool Returns true if all carts exists, false otherwise.
         */
        public function existsWhere(callable $callback): bool
        {
            foreach ($this->data as $key => $value) {
                if ($callback($key, $value)) {
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
            return $this->count() === 0;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return $this->data;
        }

        #[\Override]
        public function __toString()
        {
            return json_encode($this);
        }

        /**
         * Get an item from an iterable object
         * @param string|int $key Item to get
         * @param type $default Default value to return if no value found
         * @return mixed
         */
        public function get(string|int $key, $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }

        /**
         * Get all items which satisfies the condition provided by the callback
         * function.
         * @param callable $callback A callback function that receive an item name as
         * first parameter and an item value as second parameter. This function
         * must return a boolean value.
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
            foreach ($this->data as $key => $value) {
                if ($callback($key, $value)) {
                    $rows[$key] = $value;
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
            return $this->data;
        }

        /**
         * Convert iterable data to CSV
         * @param string $separator Data separator
         * @return string
         */
        public function toCsv(string $separator = ','): string
        {
            return DataConvertor::array2csv($this->data, $separator);
        }

        /**
         * Convert iterable data to XML
         * @return string
         */
        public function toXml(): string
        {
            return DataConvertor::array2xml($this->data);
        }

        /**
         * Convert iterable data to JSON
         * @return string
         */
        public function toJson(): string
        {
            return json_encode($this->data);
        }

        /**
         * Convert iterable data to datagrid, A json with first row as headers,
         * and second row contains a list of values corresponding to given headers
         * @return string
         */
        public function toDataGrid(): string
        {
            return DataConvertor::array2dataGrid($this->data);
        }

        /**
         * Get an iterable object as array
         * @param array $keys Items to get from an iterable object
         * @param bool $selected When true, only selected items using $keys will be returned
         * @return array
         */
        public function getAll(array $keys, bool $selected = true): array
        {
            $rows = [];
            if ($selected) {
                foreach ($keys as $key) {
                    $rows[$key] = $this->get($key);
                }
            } else {
                foreach ($this->data as $key => $value) {
                    if (!in_array($key, $keys)) {
                        $rows[$key] = $value;
                    }
                }
            }
            return $rows;
        }

        /**
         * Reduce an array to a scalar value, for example when finding sum or
         * average of an array column
         * @param callable $callback a callback that accepts three arguments,
         * an array key, array value and accumulator. The type of accumulator
         * is same as that of <code>$initialValue</code>
         * @param type $initialValue An initial accumulator value
         * @return type A single scalar value
         */
        public function reduce(callable $callback, $initialValue = null)
        {
            $accumulator = $initialValue;
            foreach ($this->data as $key => $value) {
                $accumulator = $callback($key, $value, $accumulator);
            }
            return $accumulator;
        }

        /**
         * Returns array of keys from an iterable object
         * @return array
         */
        public function keySet(): array
        {
            return array_keys($this->data);
        }

        /**
         * Get all values from an iterable object
         * @return array
         */
        public function values(): array
        {
            return array_values($this->data);
        }

        /**
         * Generate a key-value set for each item in iterable object
         * @return \Generator
         */
        public function entrySet(): \Generator
        {
            foreach ($this->data as $key => $value) {
                yield [$key => $value];
            }
        }

        /**
         * Apply a callback function for each value of a collection i.e array.
         * @param callable $callback A callback function that receives an item name
         * and value as first and second parameters.
         * @return bool
         */
        public function each(callable $callback): self
        {
            foreach ($this->data as $key => $value) {
                $callback($key, $value);
            }
            return $this;
        }
    }

}
