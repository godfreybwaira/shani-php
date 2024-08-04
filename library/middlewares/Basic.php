<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace library\middlewares {

    use shani\engine\http\App;
    use library\HttpStatus;

    final class Basic
    {

        /**
         * Block incoming CSRF attacks. All attacks coming via http GET request will
         * be discarded. User must make sure not submitting sensitive information
         * via GET request.
         * @param App $app Application Object
         * @return void
         */
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

        /**
         * Check if current application user is authenticated to access the requested
         * resource. If not, then 403 HTTP error will be raised.
         * @param App $app Application Object
         * @param bool $loggedIn Value to check if user has logged in successfully.
         * @param array $guestModules All modules that can be accessed by guest user
         * must be provided here.
         * @param array $publicModules All modules that are accessed by both guest and
         * and authenticated user must be mentioned here.
         * @return void
         */
        public static function checkAuthentication(App &$app, bool $loggedIn, array $guestModules = [], array $publicModules = []): void
        {
            $module = $app->request()->module();
            if ($loggedIn) {
                if (in_array($module, $guestModules)) {
                    $app->request()->rewriteUrl($app->config()->homepage());
                }
                return;
            }
            if (in_array($module, $guestModules) || in_array($module, $publicModules)) {
                return;
            }
            $app->response()->setStatus(HttpStatus::FORBIDDEN);
        }

        /**
         * Check if current application user has permissions enough to access
         * the current resource. If not then HTTP code 401 will be raised
         * @param App $app Application object
         * @param string $permissions List of application to search from, separated by
         * comma or any special character
         * @return void
         */
        public static function checkAuthorization(App &$app, string &$permissions): void
        {
            $code = \library\Utils::digest($app->request()->path());
            if (preg_match('\b' . $code . '\b', $permissions) !== 1) {
                $app->response()->setStatus(HttpStatus::UNAUTHORIZED);
            }
        }
    }

}
