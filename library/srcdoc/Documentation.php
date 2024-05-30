<?php

/**
 * Description of Documentation
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace library\srcdoc {

    use shani\engine\config\AppConfig;

    final class Documentation
    {

        private AppConfig $config;
        private \ReflectionClass $reflection;

        public function __construct(AppConfig &$config)
        {
            $this->config = $config;
            $this->reflection = new \ReflectionClass($config);
            $this->docs();
//            \app\test\v1\modules\users\src\get\Profile
//            /media/coder/projekt/dev/shani/v2/app/test/v1/config/Settings.php
        }

        private static function folderContent(string $rootPath, string $subDir = null)
        {
            $folders = [];
            $contents = array_diff(scandir($rootPath), ['.', '..']);
            foreach ($contents as $content) {
                echo $path = $rootPath . '/' . $content . $subDir;
                echo PHP_EOL;
                if (is_dir($path)) {
                    $folders[$content] = self::folderContent($path);
                } else {
                    $str = substr($path, 0, strpos($path, '.'));
                    return str_replace('/', '\\', substr($str, strlen(SERVER_ROOT)));
                }
            }
            return $folders;
        }

        private function docs()
        {
            $path = \shani\engine\core\Path::APP . $this->config->root() . $this->config->moduleDir();
            $modules = self::folderContent($path, $this->config->sourceDir());
            $docs = [];
            foreach ($modules as $module => $methods) {
                $docs[$module] = [];
                foreach ($methods as $method => $class) {
                    $docs[$module][] = $class;
                }
            }
            print_r($docs);
        }

        public function asArray(): array
        {
            return $this->docs(array_diff(scandir(\shani\core\Path::WEB), ['.', '..']));
        }

        public function create()
        {
            $file = $this->req->qs('f');
            $title = substr($file, 0, strpos($file, '.'));
            $class = '\\' . str_replace('/', '\\', $title);
            if (class_exists($class)) {
                $cls = explode('/', $file);
                $actions = [
                    'put' => 'Update',
                    'get' => 'View', 'delete' => 'Delete',
                    'post' => 'Create' . ($cls[0] === 'web' ? ' or update' : null)
                ];
                $reflection = new \ReflectionClass($class);
                return $this->res->write([
                            'methods' => $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
                            'ns' => $reflection->getName(),
                            'class' => strtolower($reflection->getShortName())
                                ], [
                            'title' => $file,
                            'method' => $actions[$cls[2]] ?? ucfirst($cls[2]),
                            'module' => $cls[0] === 'api' ? null : $cls[1]
                ]);
            }
            return $this->res->reply(false, 'Class not exists.');
        }
    }

}
