<?php

/**
 * Description of ReadableData
 * @author coder
 *
 * Created on: May 8, 2025 at 6:45:17 PM
 */

namespace lib\ds {

    abstract class ReadableData implements \Stringable, \JsonSerializable
    {

        protected array $data;

        public function __construct(array $data = [])
        {
            $this->data = $data;
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

        /**
         * Check if an iterable object has given item
         * @param string|int $key Items to check
         * @return bool
         */
        public abstract function exists(string|int ...$key): bool;

        /**
         * Check if all items mentioned exist in the current iterable object
         * @param callable $callback Callback function i.e <code>$callback(string|int $key, mixed $value):bool</code>
         * for a map and <code>$callback(mixed $value):bool</code> fro set
         * @return bool Returns true if all items exists, false otherwise.
         */
        public abstract function existsWhere(callable $callback): bool;

        /**
         * Get all items which satisfies the condition provided by the callback
         * function.
         * @param callable $callback A callback function i.e <code>$callback(string|int $key, mixed $val):bool</code>
         * for a map and <code>$callback(mixed $val):bool</code> for set
         * @param int|null $limit When to stop finding
         * @return array A list if items
         */
        public abstract function where(callable $callback, ?int $limit = null): array;

        /**
         * Get an iterable object as array
         * @param array $keys Items to get from an iterable object
         * @return array
         */
        public abstract function toArray(): array;

        /**
         * Convert iterable data to CSV
         * @param string $separator Data separator
         * @return string
         */
        public abstract function toCsv(string $separator = ','): string;

        /**
         * Convert iterable data to XML
         * @return string
         */
        public abstract function toXml(): string;

        /**
         * Convert iterable data to JSON
         * @return string
         */
        public abstract function toJson(): string;

        /**
         * Convert iterable data to datagrid, A json with first row as headers,
         * and second row contains a list of values corresponding to given headers
         * @return string
         */
        public abstract function toDataGrid(): string;

        /**
         * Reduce an array to a scalar value, for example when finding sum or
         * average of an array column
         * @param callable $callback a callback function i.e <code>$callback(string|int $key,mixed $val,mixed $accumulator):mixed</code>
         * for a map, and <code>$callback(mixed $val,mixed $accumulator):mixed</code> for set.
         * @param type $initialValue An initial accumulator value
         * @return type A single scalar value
         */
        public abstract function reduce(callable $callback, mixed $initialValue = null);

        /**
         * Apply a callback function for each value of a collection i.e array.
         * @param callable $callback A callback function i.e <code>$callback(string|int $key,mixed $val):void</code>
         * for Map and <code>$callback(mixed $val):void</code> for set
         * @return self
         */
        public abstract function each(callable $callback): self;
    }

}