<?php

/**
 * Cache Facade
 *
 * Provides a simplified static interface to the CacheManager.
 * This class is intended to be used as a shortcut for retrieving
 * the active cache driver instance.
 *
 * @author goddy
 * @created May 18, 2026 at 3:22:50 PM
 */

namespace features\cache {

    use shani\contracts\CacheInterface;

    /**
     * Static Cache accessor.
     *
     * Acts as a facade to the CacheManager, exposing a single
     * static method to retrieve the active cache driver.
     */
    final class Cache
    {

        /**
         * Retrieve the active cache driver instance.
         *
         * This method delegates to CacheManager::getInstance()
         * and returns the resolved cache driver.
         *
         * @return CacheInterface The active cache driver instance.
         */
        public static function instance(): CacheInterface
        {
            return CacheManager::getInstance('075b62e8c7be');
        }
    }

}
