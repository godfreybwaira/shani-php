<?php

/**
 * User application documentation generator
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\documentation {

    use shani\documentation\dto\ControllerDto;
    use shani\documentation\dto\ModuleDto;
    use shani\documentation\dto\RequestMethodDto;
    use shani\documentation\dto\UserAppDto;
    use shani\http\App;

    final class Generator
    {

        private readonly UserAppDto $userApp;

        public function __construct(App &$app)
        {
            $modulesPath = \shani\core\Framework::DIR_APPS . $app->config->root() . $app->config->moduleDir();
            $moduleColletion = self::scanModules($modulesPath, $app->config->controllers());
            $this->userApp = new UserAppDto($app->config->appName());
            foreach ($moduleColletion as $moduleName => $reqMethods) {
                $module = new ModuleDto($moduleName);
                foreach ($reqMethods as $name => $methodList) {
                    $method = new RequestMethodDto($name);
                    self::scanControllers($module, $method, $methodList);
                }
                $this->userApp->addModule($module);
            }
        }

        private static function scanControllers(ModuleDto &$module, RequestMethodDto &$method, array &$reqMethods): void
        {
            foreach ($reqMethods as $path) {
                $controller = new ControllerDto(basename($path, '.php'));
                self::scanFunctions($module->getName(), $method, $path);
                $controller->addRequestMethod($method);
                $module->addController($controller);
            }
        }

        private static function scanModules(string $modulesRootPath, string $methodsDir = null): array
        {
            $folders = [];
            $modules = array_diff(scandir($modulesRootPath), ['.', '..']);
            foreach ($modules as $module) {
                $path = $modulesRootPath . '/' . $module . $methodsDir;
                if (is_dir($path)) {
                    $folders[$module] = self::scanModules($path);
                } else {
                    $folders[] = substr($path, strlen(SERVER_ROOT));
                }
            }
            return $folders;
        }

        private static function cleanComment($str): ?string
        {
            if ($str !== false) {
                $comments = explode(PHP_EOL, $str);
                $size = count($comments) - 1;
                $result = ltrim($comments[1], " *\t\v\x00");
                for ($i = 2; $i < $size; $i++) {
                    $result .= PHP_EOL . ltrim($comments[$i], " *\t\v\x00");
                }
                return $result;
            }
            return null;
        }

        /**
         * Generate current user application documentation
         * @return array User application documentation
         */
        public function generate(): array
        {
            return $this->userApp->dto();
        }

        public static function scanFunctions(string $moduleName, RequestMethodDto &$method, string $classPath): void
        {

            $cleanPath = str_replace('/', '\\', substr($classPath, 0, strpos($classPath, '.')));
            $class = new \ReflectionClass($cleanPath);
            $functions = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            $className = $class->getShortName();
            $methodName = $method->getName();
            foreach ($functions as $fnobj) {
                $fnName = $fnobj->name();
                if (substr($fnName, 0, 2) === '__') {
                    continue;
                }
                $path = $moduleName . '/' . $className . '/' . $fnName;
                $target = strtolower($methodName . '/' . $path);
                $endpoint = '/' . str_replace('/', '/{id}/', strtolower($path));
                $comments = self::cleanComment($fnobj->getDocComment());
                $details = $methodName . ' ' . $moduleName . ' ' . $className . ' (' . $comments . ')';
                $method->addFunction(new dto\FunctionDto($fnName, $target, $endpoint, $details));
            }
        }
    }

}
