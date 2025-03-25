<?php

/**
 * Description of Cart
 *
 * @author coder
 */

namespace shani\persistence\session {

    final class Cart implements \JsonSerializable, \Stringable
    {

        public readonly string $name;
        private array $data = [];

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        /**
         * Add an item to a cart
         * @param string $key Item name
         * @param mixed $value Item value
         * @return self
         */
        public function add(string $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        /**
         * Add a list of items to a cart
         * @param array $rows 2D array contain list of arrays to add
         * @return self
         */
        public function addAll(array $rows): self
        {
            foreach ($rows as $row) {
                $key = array_key_first($row);
                $this->data[$key] = $row[$key];
            }
            return $this;
        }

        /**
         * Delete an item/list of items from a cart
         * @param string $keys Items to delete
         * @return self
         */
        public function delete(string ...$keys): self
        {
            foreach ($keys as $key) {
                unset($this->data[$key]);
            }
            return $this;
        }

        /**
         * Remove all items in a cart. The cart itself remains
         * @return self
         */
        public function clear(): self
        {
            $this->data = [];
            return $this;
        }

        /**
         * Check if a cart has given item(s)
         * @param string $keys Items to check
         * @return bool
         */
        public function has(string ...$keys): bool
        {
            foreach ($keys as $key) {
                if (!array_key_exists($key, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Count number of items in a cart
         * @return int
         */
        public function count(): int
        {
            return count($this->data);
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
         * Get an item from a cart
         * @param string $key Item to get
         * @return mixed
         */
        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }

        /**
         * Get list of items from a cart
         * @param array|null $keys A list of items to get
         * @return array A List of items
         */
        public function getAll(?array $keys = null): array
        {
            if (empty($keys)) {
                return $this->data;
            }
            $rows = [];
            foreach ($keys as $key) {
                $rows[$key] = $this->data[$key] ?? null;
            }
            return $rows;
        }

        /**
         * Apply a callback for each cart item and return true if the condition
         * on an item is satisfied.
         * @param callable $cb A callback function that receive an item name as
         * first parameter and an item value as second parameter. This function
         * must return a boolean value.
         * @return array A list if items
         */
        public function where(callable $cb): array
        {
            $rows = [];
            foreach ($this->data as $key => $value) {
                if ($cb($key, $value)) {
                    $rows[$key] = $value;
                }
            }
            return $rows;
        }
    }

}