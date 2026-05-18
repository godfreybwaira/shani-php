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

namespace features\cache {

    use features\utils\Duration;
    use shani\contracts\CacheInterface;

    /**
     * APCu-based cache implementation.
     *
     * Stores and retrieves values from APCu shared memory with
     * optional TTL (time-to-live) expiration.
     */
    final class ApcuCache implements CacheInterface
    {

        /**
         * Prefix applied to all cache keys to avoid collisions.
         *
         * @var string
         */
        private readonly string $prefix;

        public function __construct(string $prefix)
        {
            $this->prefix = $prefix;
        }

        public function getOne(string|int $key, mixed $default = null): mixed
        {
            $value = apcu_fetch($this->prefix . $key);
            return $value !== false ? $value : $default;
        }

        public function addOne(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface
        {
            apcu_store($this->prefix . $key, $value, $ttl ? $ttl->fromNow() : 0);
            return $this;
        }

        public function has(string|int $key): bool
        {
            return apcu_exists($this->prefix . $key);
        }

        public function delete(string|int $key): bool
        {
            return apcu_delete($this->prefix . $key);
        }

        public function clear(): CacheInterface
        {
            apcu_clear_cache();
            return $this;
        }

        public function remember(string|int $key, ?Duration $ttl, \Closure $callback): mixed
        {
            if ($this->has($key)) {
                return $this->getOne($key);
            }

            $value = $callback();
            $this->addOne($key, $value, $ttl);
            return $value;
        }

        public function addIfAbsent(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface
        {
            if (!$this->has($key)) {
                return $this->addOne($key, $value, $ttl);
            }
            return $this;
        }
    }

}
