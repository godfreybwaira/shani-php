<?php

/**
 * Description of BasicMiddlewares
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace library {

    use shani\engine\http\App;

    final class BasicMiddlewares
    {

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

        public static function checkAuthentication(App &$app, bool $loggedIn, array $exclusiveModules = []): void
        {
            if ($loggedIn || in_array($app->request()->module(), $exclusiveModules)) {
                return;
            }
            $app->response()->setStatus(HttpStatus::FORBIDDEN);
        }
    }

}
