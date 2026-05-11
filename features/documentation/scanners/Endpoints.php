<?php

/**
 * Description of Endpoints
 * @author goddy
 *
 * Created on: Jul 29, 2025 at 11:29:43 AM
 */

namespace features\documentation\scanners {

    use features\documentation\DigestedEndpoint;
    use features\documentation\Generator;
    use shani\http\RequestRoute;
    use shani\launcher\Framework;
    use shani\launcher\ShaniUtils;

    final class Endpoints implements \JsonSerializable
    {

        private readonly string $action, $target;
        private readonly DigestedEndpoint $digestedPermission;
        private readonly ?string $details;

        /**
         * Scan endpoints in a user application class
         * @param string $reqMethod Request method
         * @param string $moduleName Name of a module
         * @param \ReflectionMethod $method Method or a function to document
         */
        public function __construct(string $reqMethod, string $moduleName, \ReflectionMethod $method)
        {
            $comment = $method->getDocComment();
            $this->action = $method->getShortName();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : self::endpint2sentence($reqMethod, $moduleName, $this->action);
            $this->digestedPermission = self::digest($reqMethod, new RequestRoute($moduleName, $this->action));
            $this->target = strtolower('/' . $moduleName . '/{param}/' . $this->action);
        }

        public static function digest(string $requestMethod, RequestRoute $route): DigestedEndpoint
        {
            $endpoint = strtolower($requestMethod . '.' . $route->module . '.' . $route->action);
            return new DigestedEndpoint(substr(sha1($endpoint), offset: 5, length: 10), $endpoint);
        }

        public static function endpint2sentence(string $requestMethod, string $moduleName, string $functionName): string
        {
            $suffix = $functionName === Framework::HOME_FUNCTION ? null : ' ' . ShaniUtils::camel2Words($functionName);
            $verb = strtolower($requestMethod);
            $action = match ($verb) {
                'get' => 'View',
                'post' => 'Create new',
                'put', 'patch' => 'Update existing',
                default => $verb
            };
            return ucwords($action . ' ' . ShaniUtils::camel2Words($moduleName) . $suffix);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'details' => $this->details,
                'target' => $this->target,
                'permission' => $this->digestedPermission
            ];
        }
    }

}
