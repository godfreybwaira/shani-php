<?php

/**
 * Description of Controllers
 * @author goddy
 *
 * Created on: Jul 29, 2025 at 11:19:59 AM
 */

namespace features\documentation\scanners {

    use features\documentation\Generator;

    final class Controllers implements \JsonSerializable
    {

        private readonly string $path, $name, $module, $method;
        private readonly ?string $details;
        private array $endpoints = [];

        /**
         * Scan for controllers in a module
         * @param string $moduleName Module name
         * @param string $requestNethod Request method
         * @param string $classPath Controller file path
         */
        public function __construct(string $moduleName, string $requestNethod, string $classPath)
        {
            $class = str_replace('/', '\\', $classPath);
            $reflection = new \ReflectionClass(substr($class, 0, strpos($class, '.')));
            $this->module = $moduleName;
            $this->name = $reflection->getShortName();
            $this->path = str_replace('\\', '.', $reflection->getNamespaceName());

            $this->method = $requestNethod;
            $comment = $reflection->getDocComment();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : null;

            $this->collectEndpoints($reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
        }

        private function collectEndpoints(array $methods): void
        {
            foreach ($methods as $method) {
                if (substr($method->getShortName(), 0, 2) !== '__') {
                    $this->endpoints[] = new Endpoints($this->method, $this->module, $method);
                }
            }
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'details' => $this->details,
                'path' => $this->path,
                'name' => $this->name,
                'method' => $this->method,
                'endpoints' => $this->endpoints
            ];
        }
    }

}
