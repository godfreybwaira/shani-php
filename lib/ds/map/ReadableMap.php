<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\ds\map {

    use lib\DataConvertor;
    use lib\ds\ReadableData;

    class ReadableMap extends ReadableData
    {

        public function exists(string|int ...$key): bool
        {
            foreach ($key as $k) {
                if (!array_key_exists($k, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Check the absence of given keys in a collection and return all absent keys.
         * @param array $keys Keys to check for absence
         * @return array|null A collection of all absent keys, null if all keys exists
         */
        public function absentKeys(array $keys): ?array
        {
            $absentKeys = [];
            foreach ($keys as $k) {
                if (!array_key_exists($k, $this->data)) {
                    $absentKeys[] = $k;
                }
            }
            return !empty($absentKeys) ? $absentKeys : null;
        }

        public function existsWhere(callable $callback): bool
        {
            foreach ($this->data as $key => $value) {
                if ($callback($key, $value)) {
                    return true;
                }
            }
            return false;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return $this->data;
        }

        #[\Override]
        public function __toString()
        {
            return json_encode($this, JSON_UNESCAPED_SLASHES);
        }

        /**
         * Get an item from an iterable object
         * @param string|int $key Item to get
         * @param mixed $default Default value to return if no value found
         * @return mixed
         */
        public function getOne(string|int $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }

        /**
         * Check if an item in iterable object can be reduced to true or not
         * @param string|int $key Item to check
         * @return bool true if the value is truthy, false otherwise.
         */
        public function isTruthy(string|int $key): bool
        {
            return isset($this->data[$key]) && (bool) $this->data[$key];
        }

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

        public function toArray(): array
        {
            return $this->data;
        }

        public function toCsv(string $separator = ','): string
        {
            return DataConvertor::array2csv($this->data, $separator);
        }

        public function toXml(): string
        {
            return DataConvertor::array2xml($this->data);
        }

        public function toJson(bool $pretty = false): string
        {
            if ($pretty) {
                return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
            return json_encode($this->data, JSON_UNESCAPED_SLASHES);
        }

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
                    $rows[$key] = $this->getOne($key);
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
         * Get an iterable object as array and return the defaults if keys are
         * not found.
         * @param array $keys key-value pair of items to get from an iterable
         * object where key is the value to find and value is the default if
         * expected value is not found.
         * @return array
         */
        public function getOrDefault(array $keys): array
        {
            $rows = [];
            foreach ($keys as $key => $value) {
                $rows[$key] = $this->getOne($key, $value);
            }
            return $rows;
        }

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
                yield $key => $value;
            }
        }

        public function each(callable $callback): self
        {
            foreach ($this->data as $key => $value) {
                $callback($key, $value);
            }
            return $this;
        }
    }

}
