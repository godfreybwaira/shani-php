<?php

/**
 * Description of MutableData
 * @author coder
 *
 * Created on: May 8, 2025 at 6:45:17 PM
 */

namespace lib\ds {

    interface MutableData
    {

        /**
         * Remove all items in an iterable object. The object itself remains
         * @return self
         */
        public function clear(): self;

        /**
         * Add a key-value pairs of items
         * @param array $items Items to add
         * @return self
         */
        public function addAll(array $items): self;

        /**
         * Delete an item from an iterable object
         * @param string|int $item Item to delete
         * @return self
         */
        public function delete(string|int $item): self;

        /**
         * Delete all items mentioned from  an iterable object
         * @param array $items Items to delete
         * @return self
         */
        public function deleteAll(array $items): self;

        /**
         * Delete all items which satisfies the condition provided by the callback function.
         * @param callable $callback A callback function i.e <code>$callback(string|int $key, mixed $value):bool</code>
         * for a map and <code>$callback(mixed $value):bool</code> for set.
         * @return self
         */
        public function deleteWhere(callable $callback): self;

        /**
         * Apply a callback function for each value of a collection i.e array.
         * The returned value of a function overwrites the current value.
         * @param callable $callback A callback function i.e <code>$callback(string|int $key, mixed $value):mixed</code>
         * for a map and <code>$callback(mixed $value):mixed</code> for set.
         * @return self A new object
         */
        public function map(callable $callback): self;
    }

}