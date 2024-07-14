<?php

/**
 * Description of Documentor
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\engine\core {

    final class Documentor
    {

        private static function folderContent(string $rootPath, string $subDir = null)
        {
            $folders = [];
            $contents = array_diff(scandir($rootPath), ['.', '..']);
            foreach ($contents as $content) {
                $path = $rootPath . '/' . $content . $subDir;
                if (is_dir($path)) {
                    $folders[$content] = self::folderContent($path);
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
         * @param \shani\engine\http\App $app Application object
         * @return array User application documentation
         */
        public static function generate(\shani\engine\http\App &$app): array
        {
            $config = $app->config();
            $path = \shani\engine\core\Constants::DIR_APPS . $config->root() . $config->moduleDir();
            $modules = self::folderContent($path, $config->requestMethodsDir());
            $docs = [
                'name' => $config->appName(),
                'version' => $app->request()->version()
            ];
            foreach ($modules as $module => $reqMethods) {
                foreach ($reqMethods as $method => $classes) {
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
                            $details .= ($name === Constants::HOME_FUNCTION ? 'single/all' : $name) . ')';
                            $docs['modules'][$module][$className][$name][$method] = [
                                'details' => $comments ?? ucwords(strtolower($details)),
                                'id' => \library\Utils::digest(strtolower($method . '/' . $path)),
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
