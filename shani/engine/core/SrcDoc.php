<?php

/**
 * Description of SrcDoc
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\engine\core {

    use shani\engine\config\AppConfig;

    final class SrcDoc
    {

        private AppConfig $config;

        public function __construct(AppConfig &$config)
        {
            $this->config = $config;
        }

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

        public function generate(): array
        {
            $path = \shani\engine\core\Path::APP . $this->config->root() . $this->config->moduleDir();
            $modules = self::folderContent($path, $this->config->sourceDir());
            $docs = [];
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
                            $id = strtolower($method . '/' . $module . '/' . $className . '/' . $name);
                            $docs[$module][$className][$name][$method] = [
                                'details' => ucwords(str_replace('/', ' ', $id)),
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
