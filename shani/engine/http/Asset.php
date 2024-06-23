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

        private const PREFIX = '/0';

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

        public static function tryServe(App &$app): bool
        {
            $path = $app->request()->uri()->path();
            if (!self::isStaticPath($path, self::PREFIX)) {
                return false;
            }
            if ($app->request()->headers('if-none-match') === null) {
                $filepath = self::sanitizePath(substr($path, strlen(self::PREFIX)));
                $location = $app->asset()->directory($filepath);
                $app->response()->setStatus(\library\HttpStatus::OK)->setCache()->sendFile($location);
            } else {
                $app->response()->setStatus(\library\HttpStatus::NOT_MODIFIED)->send();
            }
            return true;
        }

        public function url(string $path): string
        {
            return $this->app->request()->uri()->host() . self::PREFIX . $path;
        }

        public function directory(?string $path): string
        {
            return \shani\engine\core\Path::APPS . $this->app->config()->assetDir() . $path;
        }
    }

}