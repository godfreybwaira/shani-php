<?php

/**
 * Cache Facade
 *
 * Provides a simplified static interface to the CacheManager.
 * This class is intended to be used as a shortcut for retrieving
 * the active cache driver instance.
 *
 * Note:
 *   Ensure php extension is installed and enabled APCu is enabled
 *  (`apc.enabled=1` and `apc.enable_cli=1`) and PHP-FPM is restarted for
 *  APCu cache clearing to work properly.
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
            return CacheDriverManager::getInstance('075b62e8c7be');
        }
    }

}
