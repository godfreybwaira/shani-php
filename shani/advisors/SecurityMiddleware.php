<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\advisors {

    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\advisors\web\AccessPolicy;
    use shani\http\App;

    abstract class SecurityMiddleware
    {

        protected readonly App $app;

        protected function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Check whether the client request method is allowed by the application.
         * @return self
         * @see Configuration::allowedRequestMethods()
         */
        public function passedRequestMethodCheck(): self
        {
            $methods = $this->app->config->allowedRequestMethods();
            if ($methods === '*' || str_contains($methods, $this->app->request->method)) {
                return $this;
            }
            throw HttpStatus::methodNotAllowed($this->app);
        }

        /**
         * Block incoming CSRF attacks. All attacks coming via HTTP GET request will
         * be discarded. User must make sure not submitting sensitive information
         * via GET request
         * @return self
         */
        public function csrfTest(): self
        {
            if ($this->app->config->skipCsrfProtection()) {
                return $this;
            }
            if ($this->app->config->csrfProtected()) {
                $token = $this->app->request->cookie->get($this->app->config->csrfTokenName());
                if ($token === null || !$this->app->csrfToken()->exists($token)) {
                    throw HttpStatus::notAcceptable($this->app);
                }
            }
            return $this;
        }

        /**
         * Check whether a user has enough privileges to access a target resource
         * @param string $target Route target, see self::documentation()
         * @return bool True a user has enough privileges, false otherwise.
         */
        private function accessGranted(string $target): bool
        {
            if (empty($this->app->config->permissionList)) {
                return false;
            }
            return str_contains($this->app->config->permissionList, App::digest($target));
//            return (preg_match('\b' . App::digest($target) . '\b', $this->app->config->permissionList) === 1);
        }

        /**
         * Check if current application user is authorized to access the requested
         * resource. If not, then 401 HTTP error will be raised.
         * @return self
         */
        public function authorized(): self
        {
            if ($this->app->config->skipAuthorization()) {
                return $this;
            }
            $route = $this->app->request->route();
            if ($this->app->config->authenticated) {
                if ($this->app->config->guestModule($route->module)) {
                    $this->app->request->changeRoute($this->app->config->home());
                    return $this;
                }
                if ($this->app->config->publicModule($route->module) || $this->accessGranted($route->target)) {
                    return $this;
                }
                throw HttpStatus::forbidden($this->app);
            } else if ($this->app->config->guestModule($route->module) || $this->app->config->publicModule($route->module)) {
                return $this;
            }
            throw HttpStatus::notAuthorized($this->app);
        }

        /**
         * Tells a web browser whether to allow other sites to access your resources
         * @return void
         * @see Configuration::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): self
        {
            $policy = $this->app->config->resourceAccessPolicy();
            if ($policy !== AccessPolicy::DISABLED) {
                $this->app->response->header()->addAll([
                    HttpHeader::CROSS_ORIGIN_RESOURCE_POLICY => $policy->value,
                    HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN => $this->app->config->whitelistedDomains(),
                    HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $this->app->config->allowedRequestMethods()
                ]);
            }
            return $this;
        }

        /**
         * Tells a web browser to disable other sites from embedding your website
         * to theirs, e.g via iframe tag
         * @return self
         */
        public function blockClickjacking(): self
        {
            $this->app->response->header()->add(HttpHeader::X_FRAME_OPTIONS, 'SAMEORIGIN');
//            $this->app->response->header()->set(HttpHeader::CONTENT_SECURITY_POLICY, "frame-ancestors 'self'");
            return $this;
        }

        /**
         * Check user session validity. If session expired, user is redirected back to /
         * @return self
         */
        public function validateSession(): self
        {
            if ($this->app->config->sessionEnabled() && $this->app->session()->expired()) {
                throw HttpStatus::sessionExpired($this->app);
            }
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
            if ($this->app->request->method === 'options') {
                $this->app->response->setStatus(HttpStatus::NO_CONTENT)->header()->addAll([
                    HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $this->app->config->allowedRequestMethods(),
                    HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS => $this->app->config->allowedRequestHeaders(),
                    HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN => $this->app->config->whitelistedDomains(),
                    HttpHeader::ACCESS_CONTROL_MAX_AGE => $cacheTime
                ]);
            }
            return $this;
        }
    }

}
