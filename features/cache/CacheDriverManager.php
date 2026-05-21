<?php

namespace features\cache {

    use features\storage\StorageInterface;

    /**
     * Cache Driver Manager
     *
     * Automatically selects the best available cache driver
     * (APCu > File > Array fallback).
     *
     * @author Goddy
     * @created May 18, 2026 at 1:09:51 PM
     */
    final class CacheDriverManager
    {

        public readonly string $driverName;
        private readonly ?string $host;
        private readonly ?int $port;
        private array $cache = [];
        private static CacheDriverManager $driver;

        private function __construct(string $driverName, ?string $host, ?int $port)
        {
            $this->driverName = $driverName;
            $this->host = $host;
            $this->port = $port;
        }

        public static function setDriver(string $driverName, ?string $host, ?int $port): CacheDriverManager
        {
            if (!isset(self::$driver)) {
                self::$driver = new CacheDriverManager($driverName, $host, $port);
            } else {
                throw new \RuntimeException('Could not change cache driver at this moment.');
            }
            return self::$driver;
        }

        public static function getDriver(): CacheDriverManager
        {
            return self::$driver;
        }

        public function createInstance(string $prefix): StorageInterface
        {
            if (!isset($this->cache[$prefix])) {
                $this->cache[$prefix] = match ($this->driverName) {
                    'memcached' => new MemcachedCache($prefix, $this->host, $this->port),
                    'apcu' => new ApcuCache($prefix),
                    'file' => new FileCache($prefix),
                    'auto' => self::resolveDriver($prefix),
                    default => throw new \InvalidArgumentException('Unsupported cache driver "' . $this->driverName . '"')
                };
            }
            return $this->cache[$prefix];
        }

        private static function resolveDriver(string $prefix): StorageInterface
        {
            try {
                return new ApcuCache($prefix);
            } catch (\RuntimeException) {
                return new FileCache($prefix);
            }
        }
    }

}
