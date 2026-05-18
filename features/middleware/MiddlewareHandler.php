<?php

/**
 * Middleware registration point.
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace features\middleware {

    use features\attributes\AuthorizationCheck;
    use features\attributes\CsrfCheck;
    use features\attributes\PermissionCheck;
    use features\cache\Cache;
    use shani\contracts\AttributeInterface;
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
            UtilityMiddlewares::checkRunningStatus($this->app);
            UtilityMiddlewares::handleEmptyurlPath($this->app);
            UtilityMiddlewares::setProperContentType($this->app);
            UtilityMiddlewares::preflightRequest($this->app);
            UtilityMiddlewares::passedRequestMethodCheck($this->app);
        }

        public function preResponse(): void
        {
            $policy = $this->app->config->webPolicyConfig();
            $policy->csp->addCspHeaders($this->app);
            $policy->browsingPrivacy->setPolicy($this->app);
            $policy->resourceAccess->setPolicy($this->app);
        }

        public function handleAttributes(object $instance, string $methodName): void
        {
            $cacheKey = md5($instance::class . $methodName);
            $attributes = Cache::instance()->remember($cacheKey, null, function ()use (&$instance, &$methodName) {
                $methods = new \ReflectionMethod($instance, $methodName);
                $attributes = $methods->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
                if (empty($attributes)) {
                    $classes = new \ReflectionClass($instance);
                    $attributes = $classes->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
                }
                return $attributes;
            });
            if (empty($attributes)) {
                CsrfCheck::protect($this->app);
                AuthorizationCheck::protect($this->app);
                PermissionCheck::protect($this->app);
            } else {
                foreach ($attributes as $reflection) {
                    $obj = $reflection->newInstance();
                    $obj->execute($this->app);
                }
            }
        }
    }

}
