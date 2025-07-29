<?php

/**
 * Description of Controllers
 * @author goddy
 *
 * Created on: Jul 29, 2025 at 11:19:59 AM
 */

namespace shani\documentation\scanners {

    use shani\documentation\Generator;

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
            $this->path = $reflection->getNamespaceName();

            $this->method = $requestNethod;
            $comment = $reflection->getDocComment();
            $this->details = !empty($comment) ? Generator::cleanComment($comment) : null;

            $this->endpoints = self::getEndpoints($this, $reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
        }

        private static function getEndpoints(Controllers $class, array $methods): array
        {
            $functions = [];
            foreach ($methods as $method) {
                if (substr($method->getShortName(), 0, 2) === '__') {
                    continue;
                }
                $functions[] = new Endpoints($class->method, $class->module, $method);
            }
            return $functions;
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
