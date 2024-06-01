<?php

/**
 * Description of SrcDoc
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\engine\core {

    final class SrcDoc
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

        public static function generate(\shani\engine\http\App &$app): array
        {
            $config = $app->config();
            $path = \shani\engine\core\Path::APP . $config->root() . $config->moduleDir();
            $modules = self::folderContent($path, $config->sourceDir());
            $docs = [
                'name' => $config->appName(),
                'description' => $config->appDescription(),
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
                            $id = strtolower($method . '/' . $module . '/' . $className . '/' . $name);
                            $docs['modules'][$module][$className][$name][$method] = [
                                'details' => $comments ?? ucwords(str_replace('/', ' ', $id)),
                                'signature' => \library\Utils::digest($id)
                            ];
                        }
                    }
                }
            }
            return $docs;
        }
    }

}
