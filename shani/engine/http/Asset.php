<?php

/**
 * Description of Asset
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {


    final class Asset
    {

        private App $app;

        private const ASSET_PREFIX = '/0';
        private const STORAGE_PREFIX = '/1';

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        private static function isStaticPath(string $str, string $phrase): bool
        {
            return strpos($str, $phrase . '/') === 0;
        }

        public static function sanitizePath(string $path): string
        {
            return str_replace([chr(0), '..', '//'], '', $path);
        }

        /**
         * Serve static content e.g css, images and other static files.
         * @param App $app Application object
         * @return bool True on success, false otherwise.
         */
        public static function tryServe(App &$app): bool
        {
            $path = $app->request()->uri()->path();
            $prefix = $rootPath = null;
            if (self::isStaticPath($path, self::ASSET_PREFIX)) {
                $prefix = self::ASSET_PREFIX;
                $rootPath = \shani\engine\core\Definitions::DIR_ASSETS;
            } elseif (self::isStaticPath($path, self::STORAGE_PREFIX)) {
                $prefix = self::STORAGE_PREFIX;
                $rootPath = \shani\engine\core\Definitions::DIR_APPS . $app->config()->storageDir();
            }
            if ($prefix === null) {
                return false;
            }
            if ($app->request()->headers('if-none-match') === null) {
                $location = $rootPath . self::sanitizePath(substr($path, strlen($prefix)));
                $app->response()->setStatus(\library\HttpStatus::OK)->setCache()->stream($location);
            } else {
                $app->response()->setStatus(\library\HttpStatus::NOT_MODIFIED)->send();
            }
            return true;
        }

        /**
         * Get a full qualified URL to a static asset resource
         * @param string $path Path to a static asset resource
         * @return string
         */
        public function files(string $path): string
        {
            return $this->createUrl($path, self::ASSET_PREFIX);
        }

        /**
         * Get a full qualified URL of a file from user application file storage
         * @param string $path A file path
         * @return string A file URL accessible from user application file storage
         */
        public function storage(string $path): string
        {
            return $this->createUrl($path, self::STORAGE_PREFIX);
        }

        private function createUrl(string $path, string $prefix): string
        {
            return $this->app->request()->uri()->host() . $prefix . $path;
        }
    }

}