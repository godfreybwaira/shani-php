<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace shani\advisors {

    use features\utils\Duration;
    use shani\http\HttpHeader;
    use shani\http\enums\HttpStatus;
    use shani\advisors\web\BrowsingPrivacy;
    use shani\advisors\web\ContentSecurityPolicy;
    use shani\advisors\web\ResourceAccessPolicy;
    use shani\launcher\Framework;
    use features\exceptions\CustomException;
    use shani\launcher\App;

    final class SecurityMiddleware
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->app->response->header()->addAll([
                HttpHeader::X_CONTENT_TYPE_OPTIONS => 'nosniff',
                HttpHeader::SERVER => Framework::NAME
            ]);
        }

        /**
         * Set browsing policy
         * @return self
         */
        public function setBrowsingPolicy(): self
        {
            $policy = $this->app->config->browsingPrivacy();
            if ($policy !== BrowsingPrivacy::DISABLED) {
                $this->app->response->header()->addIfAbsent(HttpHeader::REFERRER_POLICY, $policy->value);
            }
            return $this;
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
            if ($this->app->config->enableCsrfProtection() && !$this->app->config->skipCsrfTest()) {
                $tokenName = $this->app->config->csrfTokenName();
                $expectedToken = $this->app->csrfToken()->getOne($tokenName);
                $submittedToken = $this->app->request->header()->getOne($tokenName) ?? $this->app->request->body()->getOne($tokenName);
                if (empty($submittedToken) || !hash_equals($expectedToken, $submittedToken)) {
                    throw CustomException::notAcceptable($this->app, 'Invalid or missing CSRF token');
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
            if ($this->app->config->skipAuthentication()) {
                return $this;
            }
            if ($this->app->config->isAuthenticated()) {
                $request = $this->app->request;
                if ($this->app->config->accessingGuestModule()) {
                    $request->changeRoute($this->app->config->homePath());
                    return $this;
                }
                if ($this->app->config->accessingPublicModule() || $this->app->config->accessGranted($request->method, $request->route())) {
                    return $this;
                }
                throw CustomException::forbidden($this->app);
            } else if ($this->app->config->accessingGuestModule() || $this->app->config->accessingPublicModule()) {
                return $this;
            }
            throw CustomException::notAuthorized($this->app);
        }

        /**
         * Tells a web browser whether to allow other sites to access your resources
         * @return void
         * @see Configuration::resourceAccessPolicy()
         */
        public function addResourceAccessPolicy(): self
        {
            $policy = $this->app->config->resourceAccessPolicy();
            if ($policy !== ResourceAccessPolicy::DISABLED) {
                $this->app->response->header()->addAll([
                    HttpHeader::CROSS_ORIGIN_RESOURCE_POLICY => $policy->value,
                    HttpHeader::ACCESS_CONTROL_ALLOW_METHODS => $this->app->config->allowedRequestMethods()
                ]);
                $this->addAllowOrigin();
            }
            return $this;
        }

        /**
         * Adding basic Content-Security-Policy (CSP) header values
         * @return self
         */
        public function addCspHeaders(): self
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
         * A request sent by the browser before sending the actual request to verify
         * whether a server can process the incoming request.
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
                $this->addAllowOrigin();
            }
            return $this;
        }

        private function addAllowOrigin(): void
        {
            $origin = $this->app->request->header()->getOne(HttpHeader::ORIGIN);
            if (!empty($origin) && $this->app->config->whitelistedDomain($origin)) {
                $this->app->response->header()->addIfAbsent(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN, $origin);
            }
        }
    }

}
