<?php

/**
 * Description of ProjectBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\Create;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\config\PathConfig;
    use shani\launcher\Framework;

    final class ProjectBuilder implements LightBuilderInterface
    {

        public const CONFIG_DIR = 'config';

        public readonly string $namespace;
        public readonly PathConfig $config;
        public readonly string $projectName;
        private readonly string $appDirectory;
        private readonly ?string $hostname;
        private readonly ?string $defaultModule;
        private readonly ?string $defaultController;

        public function __construct(string $projectName, string $moduleName = null, string $controllerName = null)
        {
            $this->projectName = $projectName;
            $this->defaultModule = $moduleName;
            $this->defaultController = $controllerName;
            $this->appDirectory = basename(Framework::DIR_APPS);
            $this->namespace = str_replace('/', '\\', $this->appDirectory . '\\' . $projectName);
            $this->config = new PathConfig(Framework::DIR_APPS . '/' . $this->projectName, $this->getHomePath());
        }

        private function getHomePath(): string
        {
            if ($this->defaultController !== null && $this->defaultModule !== null) {
                return strtolower('/' . $this->defaultModule . '/0/' . $this->defaultController . '/0/' . Framework::HOME_FUNCTION);
            }
            return '';
        }

        public function setHostName(string $hostname): self
        {
            $this->hostname = $hostname;
            return $this;
        }

        public function getModules(): array
        {
            $modules = [];
            if ($this->exists()) {
                $folders = array_diff(scandir($this->config->root . $this->config->modules), ['.', '..']);
                foreach ($folders as $moduleName) {
                    $modules[] = new ModuleBuilder($moduleName, $this);
                }
            }
            return $modules;
        }

        private function copyCGIfiles(): void
        {
            $destination = $this->config->root . '/.cgi';
            $source = Create::ASSETS . '/cgi';
            if (Directory::copy($source, $destination)) {
                $this->cleanApacheFiles($destination);
                $this->cleanNginxFiles($destination);
            }
        }

        private function cleanApacheFiles(string $cgi)
        {
            $shaniRoot = basename(SHANI_SERVER_ROOT);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            $sslDir = substr(Framework::DIR_SSL, strlen(SHANI_SERVER_ROOT) + 1);
            ///////////////////////////////////////////
            $search = [
                '{{shani_root}}', '{{asset_dir}}', '{{ssl_dir}}', '{{storage_dir}}',
                '{{domain_name}}', '{{public_bucket}}'
            ];
            $replace = [
                $shaniRoot, $assetDir, $sslDir, $storageDir, $this->hostname,
                $this->config->publicBucket
            ];
            ///////////////////////////////////////////
            $path = $cgi . '/apache/apache.conf';
            $content = str_replace($search, $replace, file_get_contents($path));
            ///////////////////////////////////////////
            $intext = 'Cleaning Apache file:';
            $outtext = file_put_contents($path, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function cleanNginxFiles(string $cgi)
        {
            $cgiDir = basename($cgi) . '/nginx';
            $path = dirname($cgi) . '/' . $cgiDir;
            $nginxFile = $path . '/nginx.conf';
            $customFile = $path . '/custom.conf';
            $appDirpath = substr($this->config->root, strlen(dirname(SHANI_SERVER_ROOT)) + 1);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            ///////////////////////////////////////////
            $search1 = [
                '{{app_dirpath}}', '{{asset_dir}}', '{{private_bucket}}',
                '{{storage_dir}}', '{{public_bucket}}'
            ];
            $replace2 = [
                $appDirpath . '/' . $cgiDir, $assetDir, $this->config->privateBucket,
                $storageDir, $this->config->publicBucket
            ];
            $nginxContent = str_replace($search1, $replace2, file_get_contents($nginxFile));
            file_put_contents($nginxFile, $nginxContent);
            $search = ['{{shani_root}}', '{{domain_name}}'];
            $replace = [basename(SHANI_SERVER_ROOT), $this->hostname];
            $content = str_replace($search, $replace, file_get_contents($customFile));
            ///////////////////////////////////////////
            $outtext = file_put_contents($customFile, $content) !== false ? 'Success' : 'Failed';
            $intext = 'Cleaning nginx files:';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function copySettings(): void
        {
            $filename = 'Settings';
            $template = Create::ASSETS . '/settings.txt';
            $path = $this->config->root . '/' . self::CONFIG_DIR;
            $search = ['{namespace}', '{config_dir}', '{project_name}', '{home_path}', '{file_name}'];
            $replace = [$this->namespace, self::CONFIG_DIR, $this->projectName, $this->config->homePath, $filename];
            $content = str_replace($search, $replace, file_get_contents($template));
            mkdir($path, LocalStorage::FILE_MODE, true);
            ///////////////////////////////////////////
            $outtext = file_put_contents($path . '/' . $filename . '.php', $content) !== false ? 'Success' : 'Failed';
            $intext = 'Copying default setting class: ' . $filename;
            echo Formatter::formatSentence($intext, $outtext);
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->exists()) {
                echo Formatter::placeCenter('PROJECT: ' . $this->projectName, underline: true);
                $vhost = new VirtualHostBuilder($this->hostname, $this);
                $vhost->build();
                $this->copyCGIfiles();
                $this->copySettings();
            }
            $module = new ModuleBuilder($this->defaultModule, $this);
            $module->build();
            ////////////////////////////////
            if ($this->defaultController !== null) {
                $service = new ServiceBuilder($this->defaultController . 'Service', $module);
                $controller = new ControllerBuilder($this->defaultController, $module);
                $controller->build();
                $service->build();
            }
            echo 'Done' . PHP_EOL;
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->config->root);
        }
    }

}
