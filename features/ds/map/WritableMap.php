<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace features\ds\map {

    use features\ds\WritableDataInterface;

    class WritableMap extends ReadableMap implements WritableDataInterface
    {

        /**
         * Update a value using callback function. The returned value from the
         * callback become the new value. The signature of the callback
         * is <code>$updater(mixed $value):mixed</code>
         * @param string|int $key An item to update
         * @param \Closure $updater An updater callback
         * @return self
         */
        public function updateValue(string|int $key, \Closure $updater): self
        {
            $this->data[$key] = $updater($this->data[$key] ?? null);
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

        public function addMap(ReadableMap $map): self
        {
            return $this->addAll($map->toArray());
        }

        public function add(\JsonSerializable $json): self
        {
            return $this->addAll($json->jsonSerialize());
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

        public function deleteWhere(\Closure $callback): self
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

        public function map(\Closure $callback): self
        {
            $rows = [];
            foreach ($this->data as $key => $value) {
                $rows[$key] = $callback($key, $value);
            }
            return new self($rows);
        }
    }

}
