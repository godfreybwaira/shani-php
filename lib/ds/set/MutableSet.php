<?php

/**
 * Represent iterable data set with unique values
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace lib\ds\set {

    use lib\ds\MutableData;

    class MutableSet extends ReadableSet implements MutableData
    {

        /**
         * Add an item to an iterable object
         * @param string|int $value Item value to add to a list
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

        public function delete(string|int $value): self
        {
            unset($this->data[$value]);
            return $this;
        }

        public function deleteAll(array $values): self
        {
            foreach ($values as $value) {
                $this->delete($value);
            }
            return $this;
        }

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

        public function clear(): self
        {
            $this->data = [];
            return $this;
        }

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
