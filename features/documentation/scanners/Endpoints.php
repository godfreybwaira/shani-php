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
    use shani\utils\ShaniUtils;

    final class Endpoints implements \JsonSerializable
    {

        public readonly string $target;
        private readonly string $action;
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
            $this->digestedPermission = self::digest($reqMethod, RequestRoute::fromValues($moduleName, $this->action));
            $suffix = $this->action === Framework::HOME_FUNCTION ? null : '/{param}/' . ShaniUtils::camelCase2kebab($this->action);
            $this->target = strtolower('/' . $moduleName . $suffix);
        }

        public static function digest(string $requestMethod, RequestRoute $route): DigestedEndpoint
        {
            $callback = $route->action === Framework::HOME_FUNCTION ? null : '.' . $route->action;
            $endpoint = strtolower($requestMethod . '.' . $route->module . $callback);
            $digestion = substr(hash('sha256', $endpoint), offset: strlen($requestMethod), length: Framework::PERMISSION_CODE_LENGTH);
            return new DigestedEndpoint($digestion, $endpoint);
        }

        public static function endpint2sentence(string $requestMethod, string $moduleName, string $functionName): string
        {
            $suffix = $functionName === Framework::HOME_FUNCTION ? null : ' ' . ShaniUtils::splitByCase($functionName);
            $verb = strtolower($requestMethod);
            $action = match ($verb) {
                'get' => 'View',
                'post' => 'Create new',
                'put', 'patch' => 'Update existing',
                default => $verb
            };
            return ucwords($action . ' ' . ShaniUtils::splitByCase($moduleName) . $suffix);
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
