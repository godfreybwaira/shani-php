<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\advisors {

    use lib\Duration;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\advisors\web\BrowsingPrivacy;
    use shani\advisors\web\ContentSecurityPolicy;
    use shani\advisors\web\RespourceAccessPolicy;
    use shani\exceptions\CustomException;
    use shani\http\App;

    final class SecurityMiddleware
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $policy = $app->config->browsingPrivacy()->value;
            if ($policy !== BrowsingPrivacy::DISABLED) {
                $this->app->response->header()->addIfAbsent(HttpHeader::REFERRER_POLICY, $policy);
            }
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
            throw CustomException::methodNotAllowed($this->app);
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
                    throw CustomException::notAcceptable($this->app);
                }
            }
            return $this;
        }

        /**
         * Check if current application user is authorized to access the requested
         * resource. If not, then 401 HTTP error will be raised.
         * @return self
         */
        public function authorized(): self
        {
            if ($this->app->config->authenticated) {
                if ($this->app->config->accessibleByGuest()) {
                    $this->app->request->changeRoute($this->app->config->home());
                    return $this;
                }
                $route = $this->app->request->route();
                if ($this->app->config->accessibleByPublic() || $this->app->config->accessGranted($this->app->request->method, $route->module, $route->controller, $route->action)) {
                    return $this;
                }
                throw CustomException::forbidden($this->app);
            } else if ($this->app->config->accessibleByGuest() || $this->app->config->accessibleByPublic()) {
                return $this;
            }
            throw CustomException::notAuthorized($this->app);
        }

        /**
         * Tells a web browser whether to allow other sites to access your resources
         * @return void
         * @see Configuration::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): self
        {
            $policy = $this->app->config->resourceAccessPolicy();
            if ($policy !== RespourceAccessPolicy::DISABLED) {
                $this->app->response->header()->addAll([
                    HttpHeader::CROSS_ORIGIN_RESOURCE_POLICY => $policy->value,
                    HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $this->app->config->allowedRequestMethods()
                ]);
                return $this->addAllowOrigin();
            }
            return $this;
        }

        /**
         * Adding basic Content-Security-Policy (CSP) header values
         * @return self
         */
        public function cspHeaders(): self
        {
            $policy = $this->app->config->csp();
            if ($policy !== ContentSecurityPolicy::DISABLE) {
                $this->app->response->header()->addIfAbsent(HttpHeader::X_FRAME_OPTIONS, 'sameorigin');
                $this->app->response->header()->addIfAbsent(HttpHeader::CONTENT_SECURITY_POLICY, $policy->value);
                if ($this->app->request->uri->secure()) {
                    $duration = Duration::of(2, Duration::YEARS)->getTimestamp() - time();
                    $hsts = 'max-age=' . $duration . ';includeSubDomains;preload';
                    $this->app->response->header()->addIfAbsent(HttpHeader::STRICT_TRANSPORT_SECURITY, $hsts);
                }
            }
            return $this;
        }

        /**
         * Check user session validity. If session expired, user is redirected back to /
         * @return self
         */
        public function validateSession(): self
        {
            if ($this->app->config->sessionEnabled() && $this->app->session()->expired()) {
                throw CustomException::sessionExpired($this->app);
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
                    HttpHeader::ACCESS_CONTROL_MAX_AGE => $cacheTime
                ]);
                return $this->addAllowOrigin();
            }
            return $this;
        }

        private function addAllowOrigin(): self
        {
            $origin = $this->app->request->header()->get(HttpHeader::ORIGIN);
            if (!empty($origin) && $this->app->config->whitelistedDomain($origin)) {
                $this->app->response->header()->addIfAbsent(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
            }
            return $this;
        }
    }

}
