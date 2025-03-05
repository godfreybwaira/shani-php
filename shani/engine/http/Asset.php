<?php

/**
 * Description of Asset
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use library\HttpHeader;
    use library\HttpStatus;
    use shani\engine\core\Definitions;
    use shani\engine\http\bado\HttpCache;
    use shani\engine\http\bado\RequestEntity;
    use shani\engine\http\bado\ResponseEntity;

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
         * @param RequestEntity $req RequestEntity object
         * @param ResponseEntity $res ResponseEntity object
         * @return bool True on success, false otherwise.
         */
        public static function tryServe(App &$app, RequestEntity &$req, ResponseEntity &$res): bool
        {
            $path = $app->request()->uri()->path();
            $prefix = $rootPath = null;
            if (self::isStaticPath($path, self::ASSET_PREFIX)) {
                $prefix = self::ASSET_PREFIX;
                $rootPath = self::pathTo();
            } elseif (self::isStaticPath($path, self::STORAGE_PREFIX)) {
                $prefix = self::STORAGE_PREFIX;
                $rootPath = $app->storage()->pathTo();
            } elseif (self::isStaticPath($path, self::PRIVATE_PREFIX)) {
                if (!$app->config()->authenticated()) {
                    $res->setStatus(HttpStatus::UNAUTHORIZED);
                    $app->send($res);
                    return true;
                }
                $prefix = self::PRIVATE_PREFIX;
                $rootPath = $app->storage()->pathTo($app->config()->protectedStorage());
            }
            if ($prefix === null) {
                return false;
            }
            if (!$req->header()->has(HttpHeader::IF_NONE_MATCH)) {
                $filepath = $rootPath . substr($path, strlen($prefix));
                $res->setStatus(HttpStatus::OK);
                $res->header()->setCache(new HttpCache());
                $app->stream($res, $$filepath);
            } else {
                $res->setStatus(HttpStatus::NOT_MODIFIED);
                $app->send($res);
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
            return $this->app->request()->uri()->host() . self::ASSET_PREFIX . $path;
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