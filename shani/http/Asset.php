<?php

/**
 * Description of Asset
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\http {

    use library\http\HttpCache;
    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use shani\core\Definitions;

    final class Asset
    {

        private readonly App $app;

        private const ASSET_PREFIX = '/-1';
        public const STORAGE_PREFIX = '/-2';
        public const PRIVATE_PREFIX = '/-3';

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        private static function isStaticPath(string $str, string $phrase): bool
        {
            return str_starts_with($str, $phrase . '/');
        }

        /**
         * Serve static content e.g CSS, images and other static files.
         * @param App $app Application object
         * @return bool True on success, false otherwise.
         */
        public static function tryServe(App &$app): bool
        {
            $path = $app->request->uri->path;
            $prefix = $rootPath = null;
            if (self::isStaticPath($path, self::ASSET_PREFIX)) {
                $prefix = self::ASSET_PREFIX;
                $rootPath = self::pathTo();
            } elseif (self::isStaticPath($path, self::STORAGE_PREFIX)) {
                $prefix = self::STORAGE_PREFIX;
                $rootPath = $app->storage()->pathTo();
            } elseif (self::isStaticPath($path, self::PRIVATE_PREFIX)) {
                if (!$app->config->authenticated) {
                    $app->response->setStatus(HttpStatus::UNAUTHORIZED);
                    $app->send();
                    return true;
                }
                $prefix = self::PRIVATE_PREFIX;
                $rootPath = $app->storage()->pathTo($app->config->protectedStorage());
            }
            if ($prefix === null) {
                return false;
            }
            if (!$app->request->header()->has(HttpHeader::IF_NONE_MATCH)) {
                $file = $rootPath . substr($path, strlen($prefix));
                $app->response->setStatus(HttpStatus::OK)->setCache(new HttpCache());
                $app->stream($file);
            } else {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                $app->send();
            }
            return true;
        }

        /**
         * Get a full qualified URL to a static asset resource
         * @param string $path Path to a static asset resource
         * @return string
         */
        public function urlTo(string $path): string
        {
            return $this->app->request->uri->host() . self::ASSET_PREFIX . $path;
        }

        /**
         * Get a file absolute path from public (shared) asset directory
         * @param string $path A file path relative to asset directory
         * @return string
         */
        public static function pathTo(string $path = null): string
        {
            return Definitions::DIR_ASSETS . $path;
        }
    }

}