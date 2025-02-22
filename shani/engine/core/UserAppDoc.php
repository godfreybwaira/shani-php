<?php

/**
 * User application documentation generator
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\engine\core {

    use shani\engine\http\App;

    final class UserAppDoc
    {

        private static function folderContent(string $modulesRootPath, string $methodsDir = null)
        {
            $folders = [];
            $modules = array_diff(scandir($modulesRootPath), ['.', '..']);
            foreach ($modules as $module) {
                $path = $modulesRootPath . '/' . $module . $methodsDir;
                if (is_dir($path)) {
                    $folders[$module] = self::folderContent($path);
                } else {
                    $class = substr($path, 0, strpos($path, '.'));
                    $folders[] = str_replace('/', '\\', substr($class, strlen(SERVER_ROOT)));
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
         * @param App $app Application object
         * @return array User application documentation
         */
        public static function generate(App &$app): array
        {
            $config = $app->config();
            $modulesPath = Definitions::DIR_APPS . $config->root() . $config->moduleDir();
            $modules = self::folderContent($modulesPath, $config->controllers());
            $allowedMethods = $config->requestMethods();
            $docs = [
                'name' => $config->appName(),
                'version' => $app->request()->version()
            ];
            foreach ($modules as $module => $reqMethods) {
                $docs['modules'][] = self::getModules($module, $reqMethods, $allowedMethods);
            }
            return $docs;
        }

        private static function getModules(string $moduleName, array &$reqMethods, array &$allowedMethods): array
        {
            $modules = null;
            foreach ($reqMethods as $method => $classes) {
                if (!in_array($method, $allowedMethods)) {
                    continue;
                }
                $modules[$moduleName][] = self::getClasses($method, $classes);
            }
            return $modules;
        }

        private static function getClasses(string $method, array &$classes)
        {
            $classList = null;
            foreach ($classes as $key => $class) {
                $classList[$class][] = $key;
            }
            return $classList;
        }
    }

}
