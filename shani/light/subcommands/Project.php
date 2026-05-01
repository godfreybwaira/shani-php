<?php

/**
 * Description of Project
 * @author goddy
 *
 * Created on: May 1, 2026 at 10:14:58 AM
 */

namespace shani\light\subcommands {

    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\config\PathConfig;
    use shani\launcher\Framework;

    final class Project
    {

        private const SAMPLE_MODULE_NAME = 'test';
        private const CONFIG_DIR = 'config';
        private const SAMPLE_CONTROLLER_NAME = 'Sample';
        private const REQUEST_METHODS = ['get', 'post', 'delete', 'put'];

        private readonly string $name;
        private readonly string $hostname;
        private readonly string $assets;
        private readonly string $appDirectory;
        private readonly PathConfig $config;

        public function __construct(string $params, string $assets)
        {
            $project = explode('@', $params);
            if (count($project) !== 2) {
                throw new \RuntimeException('Please follow this pattern: project_name@hostname');
            }
            $this->name = $project[0];
            $this->hostname = $project[1];
            $this->assets = $assets;
            $this->appDirectory = basename(Framework::DIR_APPS);
            $homepath = '/' . self::SAMPLE_MODULE_NAME . '/0/' . self::SAMPLE_CONTROLLER_NAME . '/0/' . Framework::HOME_FUNCTION;
            $this->config = new PathConfig(Framework::DIR_APPS . '/' . $this->name, strtolower($homepath));
        }

        private function createProject(): void
        {
            if (is_dir($this->config->root)) {
                throw new \RuntimeException('Project name "' . $this->name . '" is already taken. Choose another name.');
            }
            $module = $this->config->root . $this->config->modules . '/' . self::SAMPLE_MODULE_NAME;
            $controllers = $module . $this->config->controllers;
            $views = $module . $this->config->views;
            $languages = $module . $this->config->languages;
            $data = $module . '/data';
            $entities = $data . '/entities';
            $dto = $data . '/dto';
            $enums = $data . '/enums';
            mkdir($this->config->storage, LocalStorage::FILE_MODE, true);
            $this->createService($module, dirname($this->config->controllers) . '/services');
            $this->createControllers($controllers);
            $this->createViews($views);
            $this->createLanguage($languages);
            mkdir($dto, LocalStorage::FILE_MODE, true);
            mkdir($entities, LocalStorage::FILE_MODE, true);
            mkdir($enums, LocalStorage::FILE_MODE, true);
        }

        private function createNamespace(): string
        {
            $moduleDirname = str_replace('/', '\\', trim($this->config->modules, '/'));
            return $this->appDirectory . '\\' . $this->name . '\\' . $moduleDirname . '\\' . self::SAMPLE_MODULE_NAME;
        }

        private function createViews(string $views): void
        {
            echo 'Creating view: ' . Framework::HOME_FUNCTION . PHP_EOL;
            ///////////////////////////////////////////
            $path = $views . '/' . strtolower(self::SAMPLE_CONTROLLER_NAME);
            mkdir($path, LocalStorage::FILE_MODE, true);
            if (copy($this->assets . '/view.txt', $path . '/' . Framework::HOME_FUNCTION . '.php')) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function createService(string $module, string $service): void
        {
            $serviceName = self::SAMPLE_CONTROLLER_NAME . 'Service';
            echo 'Creating service: ' . $serviceName . PHP_EOL;
            ///////////////////////////////////////////
            $namespace = $this->createNamespace();
            $search = [
                '{namespace}', '{service_dir}', '{service_name}'
            ];
            $replace = [
                $namespace, str_replace('/', '\\', trim($service, '/')), $serviceName
            ];
            $path = $module . $service;
            mkdir($path, LocalStorage::FILE_MODE, true);
            $content = str_replace($search, $replace, file_get_contents($this->assets . '/service.txt'));
            if (file_put_contents($path . '/' . $serviceName . '.php', $content) !== false) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function createLanguage(string $language): void
        {
            echo 'Creating language directory: ' . Framework::HOME_FUNCTION . PHP_EOL;
            ///////////////////////////////////////////
            $path = $language . '/' . strtolower(self::SAMPLE_CONTROLLER_NAME . '/' . Framework::HOME_FUNCTION);
            mkdir($path, LocalStorage::FILE_MODE, true);
            if (copy($this->assets . '/lang.txt', $path . '/en.php')) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function createControllers(string $controller): void
        {
            echo 'Creating controller: ' . self::SAMPLE_CONTROLLER_NAME . PHP_EOL;
            ///////////////////////////////////////////
            $namespace = $this->createNamespace();
            $search = [
                '{namespace}', '{controller_dir}', '{controller_name}', '{fn_name}'
            ];
            $replace = [
                $namespace, str_replace('/', '\\', trim($this->config->controllers, '/')),
                self::SAMPLE_CONTROLLER_NAME, Framework::HOME_FUNCTION
            ];
            $filecontent = file_get_contents($this->assets . '/controller.txt');
            foreach (self::REQUEST_METHODS as $method) {
                $path = $controller . '/' . $method;
                mkdir($path, LocalStorage::FILE_MODE, true);
                $content = str_replace([...$search, '{req_method}'], [...$replace, $method], $filecontent);
                file_put_contents($path . '/' . self::SAMPLE_CONTROLLER_NAME . '.php', $content);
            }
            echo 'Done' . PHP_EOL;
        }

        private function createHost(): void
        {
            echo 'Creating host: ' . $this->hostname . PHP_EOL;
            ///////////////////////////////////////////
            $hostfile = Framework::DIR_HOSTS . '/' . $this->hostname . '.yml';
            if (is_file($hostfile)) {
                throw new \RuntimeException('Host name "' . $this->hostname . '" is already taken. Choose another name.');
            }
            mkdir(Framework::DIR_HOSTS . '/' . $this->hostname, LocalStorage::FILE_MODE, true);
            $from = $this->assets . '/vhost.yml';
            if (copy($from, $hostfile)) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function copyConfigFile(): void
        {
            $filename = 'v1-config.yml';
            echo 'Copying configuration file: ' . $filename . PHP_EOL;
            ///////////////////////////////////////////
            $template = $this->assets . '/' . $filename;
            $search = ['{app_dir}', '{project_name}', '{config_dir}'];
            $replace = [$this->appDirectory, $this->name, self::CONFIG_DIR];
            $content = str_replace($search, $replace, file_get_contents($template));
            if (file_put_contents(Framework::DIR_HOSTS . '/' . $this->hostname . '/' . basename($template), $content) !== false) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function copyCGIfiles(): void
        {
            echo 'Copying CGI files' . PHP_EOL;
            ///////////////////////////////////////////
            $destination = $this->config->root . '/.cgi';
            $source = $this->assets . '/cgi';
            if (Directory::copy($source, $destination)) {
                echo 'Done' . PHP_EOL;
                $this->cleanApacheFiles($destination);
                $this->cleanNginxFiles($destination);
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function cleanApacheFiles(string $cgi)
        {
            echo 'Cleaning Apache file:' . PHP_EOL;
            ///////////////////////////////////////////
            $shaniRoot = basename(SHANI_SERVER_ROOT);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            $sslDir = substr(Framework::DIR_SSL, strlen(SHANI_SERVER_ROOT) + 1);

            $search = [
                '{{shani_root}}', '{{asset_dir}}', '{{ssl_dir}}', '{{storage_dir}}',
                '{{domain_name}}', '{{public_bucket}}'
            ];
            $replace = [
                $shaniRoot, $assetDir, $sslDir, $storageDir, $this->hostname,
                $this->config->publicBucket
            ];

            $path = $cgi . '/apache/apache.conf';
            $content = str_replace($search, $replace, file_get_contents($path));
            if (file_put_contents($path, $content) !== false) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function cleanNginxFiles(string $cgi)
        {
            echo 'Cleaning nginx files:' . PHP_EOL;
            ///////////////////////////////////////////
            $cgiDir = basename($cgi) . '/nginx';
            $path = dirname($cgi) . '/' . $cgiDir;
            $nginxFile = $path . '/nginx.conf';
            $customFile = $path . '/custom.conf';
            $appDirpath = substr($this->config->root, strlen(dirname(SHANI_SERVER_ROOT)) + 1);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);

            $search1 = [
                '{{app_dirpath}}', '{{asset_dir}}', '{{private_bucket}}',
                '{{storage_dir}}', '{{public_bucket}}'
            ];
            $replace2 = [
                $appDirpath . '/' . $cgiDir, $assetDir, $this->config->privateBucket,
                $storageDir, $this->config->publicBucket
            ];
            $nginxContent = str_replace($search1, $replace2, file_get_contents($nginxFile));
            if (file_put_contents($nginxFile, $nginxContent) !== false) {
                echo 'Done 1/2' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
            $search = ['{{shani_root}}', '{{domain_name}}'];
            $replace = [basename(SHANI_SERVER_ROOT), $this->hostname];
            $content = str_replace($search, $replace, file_get_contents($customFile));
            if (file_put_contents($customFile, $content) !== false) {
                echo 'Done 2/2' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        private function copySettings(): void
        {
            $filename = 'Settings';
            echo 'Copying default setting class: ' . $filename . PHP_EOL;
            ///////////////////////////////////////////
            $template = $this->assets . '/settings.txt';
            $path = $this->config->root . '/' . self::CONFIG_DIR;
            $search = ['{app_dir}', '{project_name}', '{config_dir}', '{home_path}', '{file_name}'];
            $replace = [$this->appDirectory, $this->name, self::CONFIG_DIR, $this->config->homePath, $filename];
            $content = str_replace($search, $replace, file_get_contents($template));
            mkdir($path, LocalStorage::FILE_MODE, true);
            if (file_put_contents($path . '/' . $filename . '.php', $content) !== false) {
                echo 'Done' . PHP_EOL;
            } else {
                echo 'Failed' . PHP_EOL;
            }
        }

        public function create(): void
        {
            echo PHP_EOL;
            try {
                $this->createProject();
                $this->createHost();
                $this->copyConfigFile();
                $this->copyCGIfiles();
                $this->copySettings();
            } catch (\Exception $exc) {
                echo $exc->getMessage() . PHP_EOL;
            }
        }
    }

}
