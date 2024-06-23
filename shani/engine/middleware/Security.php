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

        public static function checkAuthentication(App &$app): void
        {
            $module = $app->request()->module();
            if (in_array($module, $app->config()->publicModules())) {
                return;
            }
            if ($app->auth()->verified()) {
                if (in_array($module, $app->config()->guestModules())) {
                    $app->request()->forward($app->config()->homepage());
                }
                return;
            }
//            if (in_array($module, $app->config()->guestModules())) {
//                if ($app->authenticated()) {
//                    $app->request()->forward($app->config()->homepage());
//                }
//                return;
//            } else if ($app->authenticated()) {
//                return;
//            }
            $app->response()->setStatus(HttpStatus::FORBIDDEN);
        }

        public static function blockCSRF(App &$app): void
        {
            $csrf = $app->config()->csrf();
            if ($app->request()->method() === 'get' || $csrf === \shani\engine\core\AutoConfig::CSRF_OFF) {
                return;
            }
            $accepted = false;
            $token = $app->request()->cookies('csrf_token');
            $hashedUrl = \library\Utils::digest($app->request()->uri()->path());
            if ($csrf === \shani\engine\core\AutoConfig::CSRF_STRICT) {
                $accepted = $app->csrfToken()->get($hashedUrl) === $token;
            } else {
                $accepted = $app->csrfToken()->get($token) === $hashedUrl;
            }
            if (!$accepted) {
                $app->response()->setStatus(HttpStatus::NOT_ACCEPTABLE);
            }
        }

        public static function checkAuthorization(App &$app): void
        {
            if (!$app->auth()->hasPermission($app->request()->path())) {
                $app->response()->setStatus(HttpStatus::UNAUTHORIZED);
            }
        }
    }

}
