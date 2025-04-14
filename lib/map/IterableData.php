<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\map {

    class IterableData extends ReadableMap
    {

        /**
         * Add an item to an iterable object
         * @param stringInt $key Item name
         * @param mixed $value Item value
         * @return self
         */
        public function add(string|int $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        /**
         * Add a key-value pairs of items
         * @param array $items Items to add
         * @return self
         */
        public function addAll(array $items): self
        {
            foreach ($items as $name => $value) {
                $this->add($name, $value);
            }
            return $this;
        }

        /**
         * Add an item to an iterable object if it does not exists
         * @param stringInt $key Item name
         * @param mixed $value Item value
         * @return self
         */
        public function addIfAbsent(string|int $key, mixed $value): self
        {
            if (!array_key_exists($key, $this->data)) {
                return $this->add($key, $value);
            }
            return $this;
        }

        /**
         * Delete an item from an iterable object
         * @param string|int $key Item to delete
         * @return self
         */
        public function delete(string|int $key): self
        {
            unset($this->data[$key]);
            return $this;
        }

        /**
         * Delete all items metioned from  an iterable object
         * @param array $keys Items to delete
         * @return self
         */
        public function deleteAll(array $keys): self
        {
            foreach ($keys as $key) {
                $this->delete($key);
            }
            return $this;
        }

        /**
         * Delete all items which satisfies the condition provided by the callback
         * function.
         * @param callable $callback A callback function that receive an item name as
         * first parameter and an item value as second parameter. This function
         * must return a boolean value.
         * @return self
         */
        public function deleteWhere(callable $callback): self
        {
            foreach ($this->data as $key => $value) {
                if ($callback($key, $value)) {
                    unset($this->data[$key]);
                }
            }
            return $this;
        }

        /**
         * Remove all items in an iterable object. The object itself remains
         * @return self
         */
        public function clear(): self
        {
            $this->data = [];
            return $this;
        }

        /**
         * Apply a callback function for each value of a collection i.e array.
         * The returned value of a function overwrites the current value.
         * @param callable $callback A callback function that receives an item name
         * and value as first and second parameters.
         * @return self
         */
        public function map(callable $callback): self
        {
            foreach ($this->data as $key => $value) {
                $this->data[$key] = $callback($key, $value);
            }
            return $this;
        }
    }

}
