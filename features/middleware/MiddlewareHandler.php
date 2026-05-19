<?php

/**
 * Middleware registration point.
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace features\middleware {

    use features\attributes\security\AuthorizationCheck;
    use features\attributes\security\CsrfCheck;
    use features\attributes\security\PermissionCheck;
    use features\cache\Cache;
    use features\utils\Duration;
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
            $cacheKey = $instance::class . '-' . $methodName;
            $attributes = Cache::instance()->fetch($cacheKey, Duration::ofMonths(3), function ()use ($instance, $methodName) {
                // 1. Get method attributes (higher priority)
                $refMethod = new \ReflectionMethod($instance, $methodName);
                $methodAttributes = $refMethod->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
                // 2. Get class attributes
                $refClass = new \ReflectionClass($instance);
                $classAttributes = $refClass->getAttributes(AttributeInterface::class, \ReflectionAttribute::IS_INSTANCEOF);
                // 3. Merge them: Method overrides Class
                $attributesMap = self::getRegisteredAttributes();
                foreach ($classAttributes as $attr) {
                    $attributesMap[$attr->getName()] = $attr->getArguments();
                }
                foreach ($methodAttributes as $attr) {
                    $attributesMap[$attr->getName()] = $attr->getArguments();
                }
                return $attributesMap;
            });
            $this->execute($attributes);
        }

        private static function getRegisteredAttributes(): array
        {
            return [
                CsrfCheck::class => [],
                AuthorizationCheck::class => [],
                PermissionCheck::class => [],
            ];
        }

        private function execute(array &$attributes): void
        {
            foreach ($attributes as $class => $args) {
                $obj = new $class(...$args);
                $obj->execute($this->app);
            }
        }
    }

}
