<?php

namespace shani\contracts {

    use features\utils\Duration;

    /**
     * Cache Interface
     *
     * Standard interface for caching operations, primarily used for
     * attribute metadata caching but can be used elsewhere.
     *
     * Note:
     *   Ensure APCu is enabled (`apc.enabled=1` and `apc.enable_cli=1`)
     *   and PHP-FPM is restarted for APCu cache clearing to work properly.
     *
     * @author Goddy
     * @created May 18, 2026 at 12:26:20 PM
     */
    interface CacheInterface
    {

        /**
         * Retrieve a cached value by key.
         *
         * @param string|int $key The cache key.
         * @param mixed $default Default value if key does not exist.
         * @return mixed The cached value or default if not found.
         */
        public function getOne(string|int $key, mixed $default = null): mixed;

        /**
         * Store a value in cache.
         *
         * @param string|int $key The cache key.
         * @param mixed $value The value to store.
         * @param Duration|null $ttl The time-to-live duration for the cache entry.
         * or null to store forever
         * @return CacheInterface
         */
        public function addOne(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface;

        /**
         * Add an item to a cache if it does not exists
         * @param string|int $key Item name
         * @param mixed $value Item value
         * @param Duration|null $ttl The time-to-live duration for the cache entry
         * or null to store forever.
         * @return CacheInterface
         */
        public function addIfAbsent(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface;

        /**
         * Check if a cache key exists.
         *
         * @param string|int $key The cache key.
         * @return bool True if the key exists, false otherwise.
         */
        public function has(string|int $key): bool;

        /**
         * Delete a cache entry.
         *
         * @param string|int $key The cache key.
         * @return bool True if deleted successfully, false otherwise.
         */
        public function delete(string|int $key): bool;

        /**
         * Clear all APCu cache entries.
         *
         * @return CacheInterface.
         */
        public function clear(): CacheInterface;

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
        public function remember(string|int $key, ?Duration $ttl, \Closure $callback): mixed;

        /**
         * Update a value using callback function. The returned value from the
         * callback become the new value. The signature of the callback
         * is <code>$updater(mixed $value):mixed</code>
         *
         * @param string|int $key An item to update
         * @param Duration|null $ttl The time-to-live duration for the cache entry
         * or null to store forever
         * @param \Closure $updater An updater callback
         *
         * @return self
         */
        public function updateValue(string|int $key, ?Duration $ttl, \Closure $updater): CacheInterface;
    }

}
