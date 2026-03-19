<?php

/**
 * Description of Endpoints
 * @author goddy
 *
 * Created on: Jul 29, 2025 at 11:29:43 AM
 */

namespace shani\documentation\scanners {

    use shani\documentation\Generator;
    use shani\http\App;
    use shani\http\RequestRoute;

    final class Endpoints implements \JsonSerializable
    {

        private readonly string $hash, $path, $name, $target;
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
            $this->name = $method->getShortName();
            $controller = $method->getDeclaringClass()->getShortName();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : null;
            $endpoint = self::digest($reqMethod, new RequestRoute($moduleName, $controller, $this->name));
            $this->target = strtolower('/' . $moduleName . '/{id0}/' . $controller . '/{id1}/' . $this->name);
            $this->hash = $endpoint['hash'];
            $this->path = $endpoint['endpoint'];
        }

        public static function digest(string $requestMethod, RequestRoute $route): array
        {
            $target = $requestMethod . '.' . $route->module . '.' . $route->controller . '.' . $route->action;
            $endpoint = strtolower($target);
            return ['hash' => substr(sha1($endpoint), offset: 5, length: 10), 'endpoint' => $endpoint];
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'details' => $this->details,
                'name' => $this->name,
                'target' => $this->target,
                'path' => $this->path,
                'hash' => $this->hash
            ];
        }
    }

}
