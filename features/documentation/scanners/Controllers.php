<?php

/**
 * Description of Controllers
 * @author goddy
 *
 * @since Jul 29, 2025 at 11:19:59 AM
 */

namespace features\documentation\scanners {

    use features\documentation\Generator;

    final class Controllers implements \JsonSerializable
    {

        public readonly string $requestMethod;
        private readonly string $path, $name, $module;
        private readonly ?string $details;
        private array $endpoints = [];

        /**
         * Scan for controllers in a module
         * @param string $moduleName Module name
         * @param string $requestNethod Request method
         * @param string $controllerPath Controller file path
         */
        public function __construct(string $moduleName, string $requestNethod, string $controllerPath)
        {
            $class = str_replace('/', '\\', $controllerPath);
            $reflection = new \ReflectionClass(substr($class, 0, strpos($class, '.')));
            $this->module = $moduleName;
            $this->name = $reflection->getShortName();
            $this->path = str_replace('\\', '.', $reflection->getNamespaceName());

            $this->requestMethod = $requestNethod;
            $comment = $reflection->getDocComment();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : null;

            $this->collectEndpoints($reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
        }

        private function collectEndpoints(array $methods): void
        {
            foreach ($methods as $method) {
                if (substr($method->getShortName(), 0, 2) !== '__') {
                    $this->endpoints[] = new Endpoints($this->requestMethod, $this->module, $method);
                }
            }
        }

        public function getEndpoints(): array
        {
            return $this->endpoints;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'details' => $this->details,
                'path' => $this->path,
                'name' => $this->name,
                'method' => $this->requestMethod,
                'endpoints' => $this->endpoints
            ];
        }
    }

}
