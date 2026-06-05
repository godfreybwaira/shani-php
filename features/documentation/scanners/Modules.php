<?php

/**
 * Description of Modules
 * @author goddy
 *
 * @since Jul 29, 2025 at 2:22:42 PM
 */

namespace features\documentation\scanners {

    final class Modules implements \JsonSerializable
    {

        private array $classList = [];
        private readonly string $moduleName;

        /**
         * Generate module documentation
         * @param string $moduleName Name of the module to document
         * @param string $controllerPath Path to a module controllers
         */
        public function __construct(string $moduleName, string $controllerPath)
        {
            $this->moduleName = $moduleName;
            $reqMethods = array_diff(scandir($controllerPath), ['.', '..']);
            foreach ($reqMethods as $method) {
                $path = $controllerPath . '/' . $method;
                if (is_dir($path)) {
                    $this->scanControllers($method, $path);
                }
            }
        }

        private function scanControllers(string $reqMethod, string $path): void
        {
            $classes = array_diff(scandir($path), ['.', '..']);
            foreach ($classes as $class) {
                $classPath = $path . '/' . $class;
                if (is_file($classPath)) {
                    $this->classList[] = new Controllers($this->moduleName, $reqMethod, substr($classPath, strlen(SHANI_SERVER_ROOT)));
                }
            }
        }

        public function getClassList(): array
        {
            return $this->classList;
        }

        public static function scan(string $modulesRootPath, array $exclusion = []): array
        {
            $folders = [];
            $modules = array_diff(scandir($modulesRootPath), ['.', '..']);
            foreach ($modules as $module) {
                $path = $modulesRootPath . '/' . $module;
                if (!in_array($module, $exclusion) && is_dir($path)) {
                    $folders[] = $path;
                }
            }
            return $folders;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'name' => $this->moduleName,
                'classlist' => $this->classList
            ];
        }
    }

}
