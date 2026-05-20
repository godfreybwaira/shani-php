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

        /**
         * Singleton instance of the selected cache driver.
         *
         * @var StorageInterface
         */
        private static StorageInterface $instance;

        /**
         * Get the best available cache driver instance.
         *
         * This method ensures that only one cache driver is instantiated
         * and reused throughout the application lifecycle.
         *
         * @return StorageInterface The resolved cache driver instance.
         */
        public static function getInstance(string $prefix): StorageInterface
        {
            if (!isset(self::$instance)) {
                self::$instance = self::resolveDriver($prefix);
            }
            return self::$instance;
        }

        /**
         * Resolve the best cache driver based on environment availability.
         *
         * Priority:
         * 1. APCu (shared memory, best for production)
         * 2. File Cache (fallback if APCu unavailable)
         *
         * @return StorageInterface The resolved cache driver.
         */
        private static function resolveDriver(string $prefix): StorageInterface
        {
            // 1. APCu - Best for production (shared memory)
            if (function_exists('apcu_enabled') && apcu_enabled()) {
                return new ApcuCache($prefix);
            }
            return new FileCache($prefix);
        }
    }

}
