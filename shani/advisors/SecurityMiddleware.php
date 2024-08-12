<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\advisors {

    use shani\engine\http\App;
    use library\HttpStatus;

    abstract class SecurityMiddleware
    {

        protected App $app;

        protected function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Block incoming CSRF attacks. All attacks coming via http GET request will
         * be discarded. User must make sure not submitting sensitive information
         * via GET request
         * @return bool True if check passes, false otherwise
         */
        public function blockCSRF(): bool
        {
            $csrf = $this->app->config()->csrf();
            if ($this->app->request()->method() === 'get' || $csrf === \shani\advisors\Configuration::CSRF_OFF) {
                return true;
            }
            $accepted = false;
            $token = $this->app->request()->cookies('csrf_token');
            $hashedUrl = \library\Utils::digest($this->app->request()->uri()->path());
            if ($csrf === \shani\advisors\Configuration::CSRF_STRICT) {
                $accepted = $this->app->csrfToken()->get($hashedUrl) === $token;
            } else {
                $accepted = $this->app->csrfToken()->get($token) === $hashedUrl;
            }
            if (!$accepted) {
                $this->app->response()->setStatus(HttpStatus::NOT_ACCEPTABLE);
            }
            return $accepted;
        }

        /**
         * Check if current application user is authenticated to access the requested
         * resource. If not, then 403 HTTP error will be raised.
         * @param bool $loggedIn Value to check if user has logged in successfully.
         * @param array $guestModules All modules that can be accessed by guest user
         * must be provided here
         * @param array $publicModules All modules that are accessed by both guest and
         * and authenticated user must be mentioned here.
         * @return bool True if user is authenticated, false otherwise
         */
        protected function authenticated(bool $loggedIn, array $guestModules = [], array $publicModules = []): bool
        {
            $module = $this->app->request()->module();
            if ($loggedIn) {
                if (in_array($module, $guestModules)) {
                    $this->app->request()->rewriteUrl($this->app->config()->homepage());
                }
                return true;
            }
            if (in_array($module, $guestModules) || in_array($module, $publicModules)) {
                return true;
            }
            $this->app->response()->setStatus(HttpStatus::FORBIDDEN);
            return false;
        }

        /**
         * Check if current application user has permissions enough to access
         * the current resource. If not then HTTP code 401 will be raised
         * @param string $permissions List of application to search from, separated by
         * comma or any special character
         * @return bool True if user is authorized, false otherwise
         */
        protected function authorized(string $permissions): bool
        {
            $code = \library\Utils::digest($this->app->request()->target());
            if (preg_match('\b' . $code . '\b', $permissions) !== 1) {
                $this->app->response()->setStatus(HttpStatus::UNAUTHORIZED);
                return false;
            }
            return true;
        }

        /**
         * Implement your own logic or call parent::authorized()
         * @see parent::authorized()
         */
        public abstract function checkAuthorization(): bool;

        /**
         * Implement your own logic or call parent::authenticated()
         * @see parent::authenticated()
         */
        public abstract function checkAuthentication(): bool;
    }

}
