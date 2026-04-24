<?php

/**
 * Middleware registration point.
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace features\middleware {

    use shani\http\HttpHeader;
    use shani\launcher\App;
    use shani\launcher\Framework;

    final class MiddlewareHandler implements MiddlewareHandlerInterface
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

        public function afterResponse(): void
        {

        }

        public function preRequest(): void
        {
            UtilityMiddlewares::handleEmptyurlPath($this->app);
            UtilityMiddlewares::setProperContentType($this->app);
            UtilityMiddlewares::preflightRequest($this->app);
            $security = new SecurityMiddleware($this->app);
            $security->csrfTest();
            $security->authorized();
            $security->passedRequestMethodCheck();
        }

        public function preResponse(): void
        {
            $policy = $this->app->config->webPolicyConfig();
            $policy->csp->addCspHeaders($this->app);
            $policy->browsingPrivacy->setPolicy($this->app);
            $policy->resourceAccess->setPolicy($this->app);
        }
    }

}
