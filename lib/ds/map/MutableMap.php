<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\ds\map {

    use lib\ds\MutableData;

    class MutableMap extends ReadableMap implements MutableData
    {

        /**
         * Increment an item by a given number. A number can be negative or positive.
         * @param string|int $key An item to increment
         * @param float $value A value to increment
         * @return self
         */
        public function increment(string|int $key, float $value = 1): self
        {
            $this->data[$key] = ($this->data[$key] ?? 0) + $value;
            return $this;
        }

        /**
         * Add an item to an iterable object
         * @param string|int $key Item name
         * @param mixed $value Item value
         * @return self
         */
        public function addOne(string|int $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        /**
         * Remove an item if exists, else add it.
         * @param string $key Item to add or remove
         * @param mixed $value Item value
         * @return self
         */
        public function toggle(string $key, mixed $value = null): self
        {
            if ($this->exists($key)) {
                return $this->delete($key);
            }
            return $this->addOne($key, $value);
        }

        public function addAll(array $items): self
        {
            foreach ($items as $name => $value) {
                $this->addOne($name, $value);
            }
            return $this;
        }

        /**
         * Add an item to an iterable object if it does not exists
         * @param string|int $key Item name
         * @param mixed $value Item value
         * @return self
         */
        public function addIfAbsent(string|int $key, mixed $value): self
        {
            if (!array_key_exists($key, $this->data)) {
                return $this->addOne($key, $value);
            }
            return $this;
        }

        public function delete(string|int $key): self
        {
            unset($this->data[$key]);
            return $this;
        }

        public function deleteAll(array $keys): self
        {
            foreach ($keys as $key) {
                $this->delete($key);
            }
            return $this;
        }

        public function deleteWhere(callable $callback): self
        {
            foreach ($this->data as $key => $value) {
                if ($callback($key, $value)) {
                    unset($this->data[$key]);
                }
            }
            return $this;
        }

        public function clear(): self
        {
            $this->data = [];
            return $this;
        }

        public function map(callable $callback): self
        {
            $rows = [];
            foreach ($this->data as $key => $value) {
                $rows[$key] = $callback($key, $value);
            }
            return new self($rows);
        }
    }

}
