<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\advisors {

    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use shani\engine\http\App;
    use shani\engine\http\bado\RequestRoute;

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
            if (in_array($this->app->request()->method, $this->cnf->requestMethods())) {
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
            $method = $this->app->request()->method;
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
            $module = $this->app->request()->route()->module;
            if ($this->cnf->authenticated()) {
                if (in_array($module, $this->cnf->guestModules())) {
                    $this->app->request()->setRoute(new RequestRoute($this->cnf->homepage()));
                    return true;
                }
                if (in_array($module, $this->cnf->publicModules())) {
                    return true;
                }
                $targetId = App::digest($this->app->target());
                if ($this->app->hasAuthority($targetId)) {
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
            $this->app->response()->header()
                    ->set(HttpHeader::CROSS_ORIGIN_RESOURCE_POLICY, self::ACCESS_POLICIES[$policy])
                    ->set(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $this->cnf->whitelistedDomains())
                    ->set(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, $this->cnf->requestMethods());
        }

        /**
         * Tells a web browser to disable other sites from embedding your website
         * to theirs, e.g via iframe tag
         * @return self
         */
        public function blockClickjacking(): self
        {
            $this->app->response()->header()->set(HttpHeader::X_FRAME_OPTIONS, 'SAMEORIGIN');
//            $this->app->response()->header()->set(HttpHeader::CONTENT_SECURITY_POLICY, "frame-ancestors 'self'");
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
            if (!$this->cnf->preflightRequest() || $req->method !== 'options') {
                return $this;
            }
            $headers = $req->header()->getAll([
                HttpHeader::ACCESS_CONTROL_REQUEST_METHOD,
                HttpHeader::ACCESS_CONTROL_REQUEST_HEADERS
            ]);
            if (empty($headers[HttpHeader::ACCESS_CONTROL_REQUEST_METHOD])) {
                return $this;
            }
            $this->app->response()->setStatus(HttpStatus::NO_CONTENT)->header()
                    ->set(HttpHeader::ACCESS_CONTROL_ALLOW_METHODS, implode(',', $this->cnf->requestMethods()))
                    ->set(HttpHeader::ACCESS_CONTROL_ALLOW_HEADERS, $headers[HttpHeader::ACCESS_CONTROL_REQUEST_HEADERS] ?? '*')
                    ->set(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $this->cnf->whitelistedDomains())
                    ->set(HttpHeader::ACCESS_CONTROL_MAX_AGE, $cacheTime);
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
