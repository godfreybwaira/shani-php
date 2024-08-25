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

        private Configuration $cnf;

        protected function __construct(App &$app)
        {
            $this->app = $app;
            $this->cnf = $app->config();
        }

        /**
         * Check whether the client request method is allowed by the application.
         * @return bool
         * @see Configuration::requestMethods()
         */
        public function passedRequestMethodCheck(): bool
        {
            if (in_array($this->app->request()->method(), $this->cnf->requestMethods())) {
                return true;
            }
            $this->res->setStatus(HttpStatus::METHOD_NOT_ALLOWED);
            return false;
        }

        /**
         * Block incoming CSRF attacks. All attacks coming via HTTP GET request will
         * be discarded. User must make sure not submitting sensitive information
         * via GET request
         * @return bool True if check passes, false otherwise
         */
        public function passedCsrfTest(): bool
        {
            $method = $this->app->request()->method();
            if (!$this->cnf->csrfProtectionEnabled() || !in_array($method, $this->cnf->csrfProtectedMethods())) {
                return true;
            }
            $token = $this->app->request()->cookies($this->cnf->csrfTokenName());
            if ($token === null || !$this->app->csrfToken()->has($token)) {
                $this->app->response()->setStatus(HttpStatus::NOT_ACCEPTABLE);
                return false;
            }
            return true;
        }

        /**
         * Check if current application user is authorized to access the requested
         * resource. If not, then 401 HTTP error will be raised.
         * @return bool True on success, false otherwise
         */
        public function authorized(): bool
        {
            $permissions = $this->cnf->userPermissions();
            $module = $this->app->request()->module();
            if ($permissions !== null) {
                if (in_array($module, $this->cnf->guestModules())) {
                    $this->app->request()->rewriteUrl($this->cnf->homepage());
                    return true;
                }
                if (in_array($module, $this->cnf->publicModules())) {
                    return true;
                }
                $code = App::digest($this->app->request()->target());
                if (preg_match('\b' . $code . '\b', $permissions) === 1) {
                    return true;
                }
            } else if (in_array($module, $this->cnf->guestModules()) || in_array($module, $this->cnf->publicModules())) {
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
            $policy = $this->cnf->resourceAccessPolicy();
            if ($policy === Configuration::ACCESS_POLICY_DISABLE) {
                return;
            }
            $this->app->response()->setHeaders([
                'cross-origin-resource-policy' => self::ACCESS_POLICIES[$policy],
                'access-control-allow-origin' => $this->cnf->whitelistedDomains(),
                'access-control-allow-methods' => implode(',', $this->cnf->requestMethods())
            ]);
        }

        /**
         * Tells a web browser to disable other sites from embedding your website
         * to theirs, e.g via iframe tag
         * @return self
         */
        public function blockClickjacking(): self
        {
            $this->app->response()->setHeaders([
                'x-frame-options' => 'SAMEORIGIN',
//                'content-security-policy' => "frame-ancestors 'self'"
            ]);
            return $this;
        }

        /**
         * A request sent by the browser before sending the actual request to verify
         * whether a server can process the coming request.
         * @param int $cacheTime Tells the browser to cache the preflight response
         * @return self
         * @see Configuration::preflightRequest()
         */
        public function preflightRequest(int $cacheTime = 86400): self
        {
            $req = $this->app->request();
            if (!$this->cnf->preflightRequest() || $req->method() !== 'options') {
                return $this;
            }
            $headers = $req->headers([
                'access-control-request-method',
                'access-control-request-headers'
            ]);
            if (empty($headers['access-control-request-method'])) {
                return $this;
            }
            $this->app->response()->setStatus(HttpStatus::NO_CONTENT)->setHeaders([
                'access-control-allow-methods' => implode(',', $this->cnf->requestMethods()),
                'access-control-allow-headers' => $headers['access-control-request-headers'] ?? '*',
                'access-control-allow-origin' => $this->cnf->whitelistedDomains(),
                'access-control-max-age' => $cacheTime
            ]);
            return $this;
        }

        /**
         * Checks whether security checks is disabled.
         * @return bool
         * @see Configuration::disableSecurityAdvisor()
         */
        public function disabled(): bool
        {
            return $this->cnf->disableSecurityAdvisor();
        }
    }

}
