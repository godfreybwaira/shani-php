<?php

namespace features\cache {

    use features\utils\Duration;
    use shani\contracts\CacheInterface;

    /**
     * File-based Cache Driver
     *
     * Provides a cache implementation using the local filesystem.
     * Each cache entry is stored as a serialized file with an expiration timestamp.
     *
     * This driver is useful as a fallback when APCu is not available,
     * or for environments where persistent cache storage is required.
     *
     * @author goddy
     * @created May 18, 2026 at 1:18:11 PM
     */
    final class FileCache implements CacheInterface
    {

        /**
         * Directory path where cache files are stored.
         *
         * @var string
         */
        private readonly string $path;

        /**
         * Constructor.
         *
         * @param string $path Directory path for storing cache files.
         */
        public function __construct(string $path)
        {
            $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        /**
         * Generate the full file path for a given cache key.
         *
         * @param string|int $key The cache key.
         * @return string The corresponding file path.
         */
        private function getFilePath(string|int $key): string
        {
            return $this->path . DIRECTORY_SEPARATOR . md5($key) . '.cache';
        }

        public function getOne(string|int $key, mixed $default = null): mixed
        {
            $file = $this->getFilePath($key);
            if (!file_exists($file)) {
                return $default;
            }

            $data = unserialize(file_get_contents($file));
            if (!empty($data) && $data['expires'] > time()) {
                return $data['value'];
            }

            unlink($file);
            return $default;
        }

        public function addOne(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface
        {
            $file = $this->getFilePath($key);
            $data = [
                'value' => $value,
                'expires' => time() + ($ttl ? $ttl->fromNow() : 0)
            ];
            file_put_contents($file, serialize($data));
            return $this;
        }

        public function has(string|int $key): bool
        {
            return $this->getOne($key, '__NOT_FOUND__') !== '__NOT_FOUND__';
        }

        public function delete(string|int $key): bool
        {
            if ($this->has($key)) {
                return unlink($this->getFilePath($key));
            }
            return true;
        }

        public function clear(): CacheInterface
        {
            $files = glob($this->path . '/*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
            return $this;
        }

        public function addIfAbsent(string|int $key, mixed $value, ?Duration $ttl = null): CacheInterface
        {
            if (!$this->has($key)) {
                $this->addOne($key, $value, $ttl);
            }
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

        public function updateValue(string|int $key, ?Duration $ttl, \Closure $updater): CacheInterface
        {
            $value = $updater($this->getOne($key));
            return $this->addOne($key, $value, $ttl);
        }
    }

}
