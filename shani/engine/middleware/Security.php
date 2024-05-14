<?php

/**
 * Description of Security
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\engine\middleware {

    use shani\engine\http\App;
    use library\HttpStatus;

    final class Security
    {

        public static function errorControl(App &$app): void
        {
            ini_set('display_errors', $app->config()->development());
        }

        public static function authentication(App &$app): int
        {
            $app->web(fn() => self::webAuth($app))->api(fn() => self::apiAuth($app));
        }

        public static function blockCSRF(App &$app): void
        {
            $csrf = $app->config()->csrf();
            if ($app->request()->method() === 'get' || $csrf === \shani\engine\config\CSRF::PROTECTION_OFF) {
                return;
            }
            $accepted = false;
            $token = $app->request()->cookies('csrf_token');
            $hashedUrl = \library\Utils::digest($app->request()->uri()->path());
            if ($csrf === \shani\engine\config\CSRF::PROTECTION_STRICT) {
                $accepted = $app->token()->get($hashedUrl) === $token;
            } else {
                $accepted = $app->token()->get($token) === $hashedUrl;
            }
            if (!$accepted) {
                $app->response()->setStatus(HttpStatus::NOT_ACCEPTABLE);
            }
        }

        public static function authorization(App &$app): void
        {
            $code = \library\Utils::digest($app->request()->path());
            $roles = $app->roles();
            if ($roles === null || strpos($roles, $code) === false) {
                $app->response()->setStatus(HttpStatus::UNAUTHORIZED);
            }
        }

        private static function apiAuth(App &$app): void
        {
            $app->response()->setStatus(HttpStatus::FORBIDDEN);
        }

        private static function webAuth(App &$app): void
        {
            $module = $app->request()->module();
            if (in_array($module, $app->config()->modulePublic())) {
                return;
            }
            if ($app->authenticated()) {
                if (in_array($module, $app->config()->moduleGuest())) {
                    $app->request()->forward($app->config()->homeAuth());
                }
                return;
            }
//            if (in_array($module, $app->config()->moduleGuest())) {
//                if ($app->authenticated()) {
//                    $app->request()->forward($app->config()->homeAuth());
//                }
//                return;
//            } else if ($app->authenticated()) {
//                return;
//            }
            $app->response()->setStatus(HttpStatus::FORBIDDEN);
        }
    }

}
