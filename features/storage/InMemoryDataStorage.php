<?php

/**
 * Description of InMemoryDataStorage
 * @author goddy
 *
 * @since Apr 5, 2026 at 6:58:47 PM
 */

namespace features\storage {

    use features\ds\map\WriteMap;
    use features\storage\StorageInterface;

    final class InMemoryDataStorage implements StorageInterface
    {

        /**
         * In‑memory collection of named containers.
         *
         * Each entry is a WriteMap instance representing a
         * logical cache bucket (e.g., cart, wishlist, comparison).
         * These are lazily initialized and persisted to storage
         * when close() is invoked.
         *
         * @var array<string, WriteMap>
         */
        private array $carts = [];

        /**
         * Prefix applied to all cache keys to avoid collisions.
         *
         * @var string
         */
        private readonly string $prefix;

        /**
         * Create a file cache storage
         *
         * @param string $prefix Prefix applied to all keys to avoid collisions.
         */
        public function __construct(string $prefix)
        {
            $this->prefix = $prefix;
        }

        public final function containerExists(string $cartName): bool
        {
            return isset($this->carts[$this->prefix][$cartName]);
        }

        public function container(string $cartName): WriteMap
        {
            if (!isset($this->carts[$this->prefix][$cartName])) {
                $this->carts[$this->prefix][$cartName] = new WriteMap();
            }
            return $this->carts[$this->prefix][$cartName];
        }

        public function close(): void
        {
            return;
        }

        public function destroy(): void
        {
            unset($this->carts[$this->prefix]);
            return;
        }

        public function clear(): StorageInterface
        {
            $this->carts[$this->prefix] = [];
            return $this;
        }

        public function refresh(): StorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            return true;
        }
    }

}
