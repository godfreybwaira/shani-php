<?php

/**
 * Description of Asset
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use shani\engine\core\Directory;

    final class Asset
    {

        private App $app;

        private const PREFIX = '/0';

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        private static function isStaticPath(string $str, string $phrase): bool
        {
            return strpos($str, $phrase . '/') === 0;
        }

        public static function tryServe(App &$app): bool
        {
            $path = $app->request()->uri()->path();
            if (!self::isStaticPath($path, self::PREFIX)) {
                return false;
            }
            if ($app->request()->headers('if-none-match') === null) {
                $filepath = substr($path, strlen(self::PREFIX));
                $location = \shani\engine\core\Path::ASSET_PUBLIC . $filepath;
                $storage = Directory::ASSET_STORAGE;
                if (self::isStaticPath($filepath, $storage)) {
                    $location = $app->host()->storage() . substr($filepath, strlen($storage));
                }
                $app->response()->setStatus(\library\HttpStatus::OK)->setCache()->sendFile($location);
            } else {
                $app->response()->setStatus(\library\HttpStatus::NOT_MODIFIED)->sendHeaders();
            }
            return true;
        }

        public function private(?string $path = null): string
        {
            return \shani\engine\core\Path::ASSET_PRIVATE . $this->app->host()->storage() . $path;
        }

        public function storage(string $path): string
        {
            return $this->public(Directory::ASSET_STORAGE . $this->app->host()->storage() . $path);
        }

        public function css(string $path): string
        {
            return $this->public(Directory::ASSET_CSS . $path . '.css');
        }

        public function font(string $path): string
        {
            return $this->public(Directory::ASSET_FONTS . $path);
        }

        public function js(string $path): string
        {
            return $this->public(Directory::ASSET_JS . $path . '.js');
        }

        public function public(string $path): string
        {
            return $this->app->request()->uri()->host() . self::PREFIX . $path;
        }

        public function disk(string $path): string
        {
            return \shani\engine\core\Path::ASSET_STORAGE . $this->app->host()->storage() . $path;
        }
    }

}