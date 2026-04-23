<?php

/**
 * Optional out-of-the-box useful middlewares that user application can re-use
 * @author coder
 *
 * Created on: Feb 13, 2024 at 10:56:16 AM
 */

namespace features\middleware {

    use features\exceptions\CustomException;
    use shani\contracts\Configuration;
    use shani\http\RequestRoute;
    use shani\launcher\App;

    final class SecurityMiddleware
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        /**
         * Check whether the client request method is allowed by the application.
         * @return void
         * @see Configuration::allowedRequestMethods()
         */
        public function passedRequestMethodCheck(): void
        {
            $methods = $this->app->config->allowedRequestMethods();
            if ($methods === '*' || str_contains($methods, $this->app->request->method)) {
                return;
            }
            throw CustomException::methodNotAllowed($this->app);
        }

        /**
         * Block incoming CSRF attacks. All attacks coming via HTTP GET request will
         * be discarded. User must make sure not submitting sensitive information
         * via GET request
         * @return void
         */
        public function csrfTest(): void
        {
            if (!$this->app->config->enableCsrfProtection() || $this->app->config->skipCsrfTest()) {
                return;
            }
            $tokenName = $this->app->config->csrfTokenName();
            $expectedToken = $this->app->csrfToken()->getOne($tokenName);
            $submittedToken = $this->app->request->header()->getOne($tokenName) ?? $this->app->request->body()->getOne($tokenName);
            if (empty($submittedToken) || !hash_equals($expectedToken, $submittedToken)) {
                throw CustomException::notAcceptable($this->app, 'Invalid or missing CSRF token');
            }
        }

        /**
         * Check if current application user is authorized to access the requested
         * resource. If not, then 401 HTTP error will be raised.
         * @return void
         */
        public function authorized(): void
        {
            if ($this->app->config->skipAuthentication() || $this->app->config->accessingPublicResource()) {
                return;
            }
            if ($this->app->config->accessingGuestResource()) {
                if ($this->app->auth->loggedIn()) {
                    $route = RequestRoute::fromPath($this->app->config->homePath());
                    $this->app->request->changeRoute($route);
                }
                return;
            }
            if (!$this->app->auth->attemptAuthentication()) {
                throw CustomException::notAuthorized($this->app);
            }
            if (!$this->app->auth->accessGranted()) {
                throw CustomException::forbidden($this->app);
            }
        }
    }

}
