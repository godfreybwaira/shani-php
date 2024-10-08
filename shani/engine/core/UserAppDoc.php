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
                    $str = substr($path, 0, strpos($path, '.'));
                    $folders[] = str_replace('/', '\\', substr($str, strlen(SERVER_ROOT)));
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
            $allMethods = $config->requestMethods();
            $docs = [
                'name' => $config->appName(),
                'version' => $app->request()->version()
            ];
            foreach ($modules as $module => $reqMethods) {
                foreach ($reqMethods as $method => $classes) {
                    if (!in_array($method, $allMethods)) {
                        continue;
                    }
                    foreach ($classes as $class) {
                        $rf = new \ReflectionClass($class);
                        $functions = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
                        $className = $rf->getShortName();
                        foreach ($functions as $fnobj) {
                            $name = $fnobj->getName();
                            if (substr($name, 0, 2) === '__') {
                                continue;
                            }
                            $comments = self::cleanComment($fnobj->getDocComment());
                            $path = $module . '/' . $className . '/' . $name;
                            $details = $method . ' ' . $module . ' ' . $className . ' (';
                            $details .= ($name === Definitions::HOME_FUNCTION ? 'one/all' : $name) . ')';
                            $docs['modules'][$module][$className][$name][$method] = [
                                'details' => $comments ?? ucwords(strtolower($details)),
                                'id' => App::digest(strtolower($method . '/' . $path)),
                                'path' => '/' . str_replace('/', '/{id}/', strtolower($path))
                            ];
                        }
                    }
                }
            }
            return $docs;
        }
    }

}
