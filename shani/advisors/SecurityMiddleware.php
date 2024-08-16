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

        private const ACCESS_POLICIES = [
            Configuration::ACCESS_POLICY_ANY_DOMAIN => 'cross-origin',
            Configuration::ACCESS_POLICY_THIS_DOMAIN => 'same-origin',
            Configuration::ACCESS_POLICY_THIS_DOMAIN_AND_SUBDOMAIN => 'same-site'
        ];
        private const REFERRER_PRIVACIES = [
            Configuration::BROWSING_PRIVACY_STRICT => 'no-referrer',
            Configuration::BROWSING_PRIVACY_THIS_DOMAIN => 'same-origin',
            Configuration::BROWSING_PRIVACY_PARTIALLY => 'strict-origin',
            Configuration::BROWSING_PRIVACY_NONE => 'strict-origin-when-cross-origin'
        ];

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
            $hashedUrl = App::digest($this->app->request()->uri()->path());
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
         * Check if current application user is authorized to access the requested
         * resource. If not, then 401 HTTP error will be raised.
         * @return bool True on success, false otherwise
         */
        public function authorized(): bool
        {
            $cnf = $this->app->config();
            $permissions = $cnf->userPermissions();
            $module = $this->app->request()->module();
            if ($permissions !== null) {
                if (in_array($module, $cnf->guestModules())) {
                    $this->app->request()->rewriteUrl($cnf->homepage());
                    return true;
                }
                if (in_array($module, $cnf->publicModules())) {
                    return true;
                }
                $code = App::digest($this->app->request()->target());
                if (preg_match('\b' . $code . '\b', $permissions) === 1) {
                    return true;
                }
            } else if (in_array($module, $cnf->guestModules()) || in_array($module, $cnf->publicModules())) {
                return true;
            }
            $this->app->response()->setStatus(HttpStatus::UNAUTHORIZED);
            return false;
        }

        /**
         * Tells a web browser whether to allow other sites to access your resources
         * @return void
         * @see Configuration::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): void
        {
            $cnf = $this->app->config();
            $policy = $cnf->resourceAccessPolicy();
            if ($policy === Configuration::ACCESS_POLICY_DISABLE) {
                return;
            }
            $this->app->response()->setHeaders([
                'cross-origin-resource-policy' => self::ACCESS_POLICIES[$policy],
                'access-control-allow-origin' => $cnf->resourceAccessWhitelist(),
                'access-control-allow-methods' => implode(',', $cnf->requestMethods())
            ]);
        }

        /**
         * Tells a web browser to disable other sites from embedding your website
         * to theirs, e.g via iframe tag
         * @return void
         */
        public function blockClickjacking(): void
        {
            $this->app->response()->setHeaders([
                'x-frame-options' => 'SAMEORIGIN',
                'content-security-policy' => "frame-ancestors 'self'"
            ]);
        }

        /**
         * Tells a web browser how send HTTP referrer header. This is important
         * for keeping user browsing privacy
         * @return void
         * @see Configuration::browsingPrivacy()
         */
        public function browsingPrivacy(): void
        {
            $this->app->response()->setHeaders([
                'referrer-policy' => self::REFERRER_PRIVACIES[$this->app->config()->browsingPrivacy()]
            ]);
        }

        /**
         * A request sent by the browser before sending the actual request to verify
         * whether a server can process the coming request.
         * @param int $cacheTime Tells the browser to cache the preflight response
         * @return void
         */
        public function preflightRequest(int $cacheTime = 86400): void
        {
            $req = $this->app->request();
            if ($req->method() !== 'options') {
                return;
            }
            $headers = $req->headers([
                'access-control-request-method',
                'access-control-request-headers'
            ]);
            if (empty($headers['access-control-request-method'])) {
                return;
            }
            $this->app->response()->setStatus(HttpStatus::NO_CONTENT)->setHeaders([
                'access-control-allow-methods' => implode(',', $this->app->config()->requestMethods()),
                'access-control-allow-headers' => $headers['access-control-request-headers'] ?? '*',
                'access-control-allow-origin' => $this->app->config()->resourceAccessWhitelist(),
                'access-control-max-age' => $cacheTime
            ]);
        }
    }

}
