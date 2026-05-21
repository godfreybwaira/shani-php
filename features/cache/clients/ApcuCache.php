<?php

/**
 * APCu Cache Driver
 *
 * Provides a cache implementation using APCu shared memory.
 * This driver is best suited for production environments where
 * APCu is available and enabled.
 *
 * @author goddy
 * @created May 18, 2026 at 1:28:14 PM
 */

namespace features\cache\clients {

    use features\ds\map\WritableMap;
    use features\storage\StorageInterface;

    /**
     * APCu-based cache implementation.
     *
     * Stores and retrieves values from APCu shared memory with
     * optional TTL (time-to-live) expiration.
     */
    final class ApcuCache implements StorageInterface
    {

        /**
         * Flag indicating whether the cache has already been closed.
         *
         * Used to prevent multiple flushes of in‑memory containers
         * to the underlying storage during shutdown.
         *
         * @var bool
         */
        private bool $closed = false;

        /**
         * In‑memory collection of named containers.
         *
         * Each entry is a WritableMap instance representing a
         * logical cache bucket (e.g., cart, wishlist, comparison).
         * These are lazily initialized and persisted to storage
         * when close() is invoked.
         *
         * @var array<string, WritableMap>
         */
        private array $carts = [];

        /**
         * Prefix applied to all cache keys to avoid collisions.
         *
         * @var string
         */
        private readonly string $prefix;

        /**
         * Prefix applied to all cache keys to avoid collisions.
         *
         * @param string $prefix
         */
        public function __construct(string $prefix)
        {
            if (!function_exists('apcu_enabled') || !apcu_enabled()) {
                throw new \RuntimeException('Please enable APCU cache or choose another cache driver.');
            }
            $this->prefix = $prefix;
            register_shutdown_function([$this, 'close']);
        }

        public function container(string $name): WritableMap
        {
            if (!isset($this->carts[$name])) {
                $value = $this->getValue($name);
                $this->carts[$name] = new WritableMap($value ?? []);
            }
            return $this->carts[$name];
        }

        /**
         * Fetches a container value from APCu.
         */
        private function getValue(string $name): mixed
        {
            $key = $this->makeKey($name);
            $value = apcu_fetch($key);
            return is_array($value) ? $value : null;
        }

        public function containerExists(string $name): bool
        {
            return apcu_exists($this->makeKey($name));
        }

        public function destroy(): void
        {
            $this->clear();
        }

        public function clear(): StorageInterface
        {
            foreach ($this->carts as $name => $v) {
                apcu_delete($this->makeKey($name));
            }
            $this->carts = [];
            return $this;
        }

        public function refresh(): StorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            return function_exists('apcu_enabled') && apcu_enabled();
        }

        public function close(): void
        {
            if (!$this->closed) {
                foreach ($this->carts as $name => $cart) {
                    apcu_store($this->makeKey($name), $cart->toArray());
                }
                $this->closed = true;
            }
        }

        /**
         * Builds a unique APCu key for a container.
         */
        private function makeKey(string $name): string
        {
            return $this->prefix . '-' . $name;
        }
    }

}
