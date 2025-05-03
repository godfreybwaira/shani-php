<?php

/**
 * Represent iterable data set with unique values
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\set {

    class MutableSet extends ReadableSet
    {

        /**
         * Add an item to an iterable object
         * @param string|int $value Item value to add to a list
         * @param mixed $value Item value
         * @return self
         */
        public function addOne(string|int $value): self
        {
            if ($value !== '') {
                $this->data[$value] = null;
            }
            return $this;
        }

        /**
         * Remove an item if exists, else add it.
         * @param string|int $value Item to add or remove
         * @return self
         */
        public function toggle(string|int $value): self
        {
            if ($this->exists($value)) {
                return $this->delete($value);
            }
            return $this->addOne($value);
        }

        /**
         * Add a list of items
         * @param array $values Items to add
         * @return self
         */
        public function addAll(array $values): self
        {
            foreach ($values as $value) {
                $this->addOne($value);
            }
            return $this;
        }

        /**
         * Add an item to an iterable object if it does not exists
         * @param string|int $value Item value
         * @return self
         */
        public function addIfAbsent(string|int $value): self
        {
            if (!$this->exists($value)) {
                return $this->addOne($value);
            }
            return $this;
        }

        /**
         * Delete an item from an iterable object
         * @param string|int $value Item to delete
         * @return self
         */
        public function delete(string|int $value): self
        {
            unset($this->data[$value]);
            return $this;
        }

        /**
         * Delete all items mentioned from  an iterable object
         * @param array $values Items to delete
         * @return self
         */
        public function deleteAll(array $values): self
        {
            foreach ($values as $value) {
                $this->delete($value);
            }
            return $this;
        }

        /**
         * Delete all items which satisfies the condition provided by the callback
         * function.
         * @param callable $callback A callback function that receive an item.
         * This function must return a boolean value.
         * @return self
         */
        public function deleteWhere(callable $callback): self
        {
            $values = $this->toArray();
            foreach ($values as $value) {
                if ($callback($value)) {
                    $this->delete($value);
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
         * @param callable $callback A callback function that receives an item
         * as an argument.
         * @return self A new object
         */
        public function map(callable $callback): self
        {
            $set = new self();
            $values = $this->toArray();
            foreach ($values as $value) {
                $set->addOne($callback($value));
            }
            return $set;
        }
    }

}
