<?php

namespace features\cache {

    use features\storage\LocalStorage;
    use shani\contracts\CacheInterface;
    use shani\launcher\Framework;

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
         * @var CacheInterface
         */
        private static CacheInterface $instance;

        /**
         * Get the best available cache driver instance.
         *
         * This method ensures that only one cache driver is instantiated
         * and reused throughout the application lifecycle.
         *
         * @return CacheInterface The resolved cache driver instance.
         */
        public static function getInstance(string $prefix): CacheInterface
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
         * @return CacheInterface The resolved cache driver.
         */
        private static function resolveDriver(string $prefix): CacheInterface
        {
            // 1. APCu - Best for production (shared memory)
            if (function_exists('apcu_enabled') && apcu_enabled()) {
                return new ApcuCache($prefix);
            }

            // 2. File Cache - Good fallback
            $fileCachePath = Framework::DIR_SERVER_STORAGE . '/cache/' . $prefix;
            if (!is_dir($fileCachePath)) {
                mkdir($fileCachePath, LocalStorage::FILE_MODE, true);
            }
            return new FileCache($fileCachePath);
        }
    }

}
