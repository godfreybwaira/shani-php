<?php

namespace features\cache {

    use features\ds\map\WritableMap;
    use features\storage\LocalStorage;
    use features\storage\StorageInterface;
    use features\utils\Directory;
    use shani\launcher\Framework;

    /**
     * File-based Cache Driver
     *
     * Provides a cache implementation using the local filesystem.
     * Each container is stored as a serialized file with optional TTL.
     *
     * This driver is useful as a fallback when APCu is not available,
     * or for environments where persistent cache storage is required.
     *
     * @author goddy
     * @created May 20, 2026 at 9:30 AM
     */
    final class FileCache implements StorageInterface
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
         * Directory path where cache files are stored.
         */
        private readonly string $directory;

        /**
         * Prefix applied to all cache keys to avoid collisions.
         *
         * @var string
         */
        private readonly string $prefix;

        /**
         * Create a file cache storage
         *
         * @param string $prefix Prefix applied to all cache keys to avoid collisions.
         */
        public function __construct(string $prefix)
        {
            $this->prefix = $prefix;
            $this->directory = Framework::DIR_SERVER_STORAGE . '/cache/' . bin2hex($prefix);
            if (!is_dir($this->directory)) {
                mkdir($this->directory, LocalStorage::FILE_MODE, true);
            }
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
         * Fetches a container value from a file.
         */
        private function getValue(string $name): mixed
        {
            $file = $this->makeKey($name);
            if (!file_exists($file)) {
                return null;
            }
            return unserialize(file_get_contents($file));
        }

        public function containerExists(string $name): bool
        {
            return $this->getValue($name) !== null;
        }

        public function destroy(): void
        {
            Directory::delete($this->directory);
            $this->carts = [];
        }

        public function clear(): StorageInterface
        {
            $this->destroy();
            mkdir($this->directory, LocalStorage::FILE_MODE, true);
            return $this;
        }

        public function refresh(): StorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            return is_writable($this->directory);
        }

        public function close(): void
        {
            if (!$this->closed) {
                foreach ($this->carts as $name => $cart) {
                    file_put_contents($this->makeKey($name), serialize($cart->toArray()));
                }
                $this->closed = true;
            }
        }

        private function makeKey(string $name): string
        {
            return $this->directory . DIRECTORY_SEPARATOR . bin2hex($name) . '.cache';
        }
    }

}
