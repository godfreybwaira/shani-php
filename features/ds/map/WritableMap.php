<?php

/**
 * Represent iterable data objects such as array or map
 * @author coder
 *
 * Created on: Mar 26, 2025 at 9:00:14 AM
 */

namespace features\ds\map {

    use features\ds\WritableDataInterface;
    use features\utils\Duration;

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
         * Retrieve a cached value or compute and store it if missing.
         *
         * @param string|int $key The cache key.
         * @param Duration|null $ttl The time-to-live duration for the cache entry
         * @param \Closure $callback Callback to compute the value if not cached.
         * The signature is <code>$callback():mixed</code>
         * or null to store forever
         *
         * @return mixed The cached or newly computed value.
         */
        public function fetch(string|int $key, ?Duration $ttl, \Closure $callback): mixed
        {
            if ($this->exists($key)) {
                $stored = $this->getOne($key);
                if (!isset($stored['_value_'])) {
                    return $stored;
                }
                if (!Duration::expired($stored['_expires_'])) {
                    return $stored['_value_'];
                }
                $this->delete($key);
            }
            $value = $callback();
            $this->addOne($key, [
                '_value_' => $value,
                '_expires_' => $ttl?->toDateTime()->getTimestamp() ?? PHP_INT_MAX,
            ]);
            return $value;
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
            foreach ($this->data as $key => $value) {
                $this->data[$key] = $callback($key, $value);
            }
            return $this;
        }
    }

}
