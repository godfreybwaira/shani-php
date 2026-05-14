<?php

/**
 * Description of ProjectVersionBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\config\PathConfig;

    final class ProjectVersionBuilder implements LightBuilderInterface
    {

        private const CONFIG_DIR = 'config';
        private const CONFIG_FILE = 'config.yml';
        public const DEFAULT_MODULE = 'users';

        public readonly VirtualHostBuilder $vhost;
        public readonly string $namespace;
        public readonly string $versionNumber;
        private readonly PathConfig $config;
        private readonly ModuleName $defaultModule;
        private readonly string $versionName;
        private readonly string $rootPath;
        private readonly string $configFilepath;

        public function __construct(VirtualHostBuilder $vhost, string $versionNumber, string $versionName = null)
        {
            $this->vhost = $vhost;
            $this->versionNumber = $versionNumber;
            $this->versionName = $versionName ?? $versionNumber;
            $this->rootPath = $vhost->metadata->projectDirectory . DIRECTORY_SEPARATOR . $versionNumber;
            $this->namespace = str_replace('/', '\\', trim(substr($this->rootPath, strlen(SHANI_SERVER_ROOT)), DIRECTORY_SEPARATOR));
            $this->configFilepath = $this->vhost->metadata->hostDirectory . DIRECTORY_SEPARATOR . $this->versionNumber . '-' . self::CONFIG_FILE;

            $this->defaultModule = ModuleName::create('users');
        }

        private function registerVersion(): void
        {
            if ($this->configExists()) {
                throw new \RuntimeException('Project version "' . $this->versionName . '" already registered.');
            }

            $name = $this->versionName ?? $this->versionNumber;
            $versionTemplate = file_get_contents(CommandContract::ASSETS . '/version.yml');
            $search = ['{version_number}', '{version_name}', '{config_file}'];
            $replace = [$this->versionNumber, $name, basename($this->configFilepath)];
            $versionContent = str_replace($search, $replace, $versionTemplate);

            $placeholder = '####v1#';
            $hostTemplate = file_get_contents($this->vhost->metadata->hostPath);
            $hostContent = str_replace($placeholder, $versionContent . PHP_EOL . $placeholder, $hostTemplate);

            if (file_put_contents($this->vhost->metadata->hostPath, $hostContent) === false) {
                throw new \RuntimeException('Project version "' . $name . '" could not be created.');
            }
        }

        private function createConfigFile(): string
        {
            $template = CommandContract::ASSETS . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$this->namespace, self::CONFIG_DIR];
            $templateContent = str_replace($search, $replace, file_get_contents($template));

            $outtext = file_put_contents($this->configFilepath, $templateContent) !== false ? 'Success' : 'Failed';
            return Formatter::formatSentence('Creating configuration file: ' . basename($this->configFilepath), $outtext);
        }

        private function prepareSettings(): string
        {
            $filename = 'Settings';
            $settingTemplate = CommandContract::ASSETS . DIRECTORY_SEPARATOR . 'settings.txt';

            $search = ['{namespace}', '{config_dir}', '{home_path}', '{file_name}'];
            $replace = [$this->namespace, self::CONFIG_DIR, $this->defaultModule->pathName, $filename];
            $settingContent = str_replace($search, $replace, file_get_contents($settingTemplate));

            $configDirectory = $this->rootPath . DIRECTORY_SEPARATOR . self::CONFIG_DIR;
            mkdir($configDirectory, LocalStorage::FILE_MODE, true);
            $filepath = $configDirectory . DIRECTORY_SEPARATOR . $filename . '.php';

            $outtext = file_put_contents($filepath, $settingContent) !== false ? 'Success' : 'Failed';
            return Formatter::formatSentence('Creating setting class: ' . $filename, $outtext);
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if ($this->exists()) {
                throw new \RuntimeException('Project version "' . $this->versionName . '" already exists');
            }
            $this->registerVersion();
            $progressTracker($this->createConfigFile());
            $progressTracker($this->prepareSettings());

            $module = new ModuleBuilder($this->defaultModule, $this);
            $module->build($progressTracker);
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->rootPath);
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->rootPath);
            }
        }

        public function delete(\Closure $progressTracker): void
        {
            if (!$this->exists()) {
                throw new \InvalidArgumentException('Project version "' . $this->versionNumber . '" does not exists.');
            }
            $intext = 'Deleting project version "' . $this->versionNumber . '"';
            $outtext = Directory::delete($this->rootPath) ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence($intext, $outtext));
            if ($this->configExists()) {
                $outtext = unlink($this->configFilepath) ? 'Success' : 'Failed';
                $intext = 'Deleting configuration file "' . basename($this->configFilepath) . '"';
                $progressTracker(Formatter::formatSentence($intext, $outtext));
            }
        }

        public function configExists(): bool
        {
            return is_file($this->configFilepath);
        }

        public static function fromProjectName(string $projectName, string $versionNumber): ProjectVersionBuilder
        {
            $project = ProjectBuilder::fromName($projectName);
            return new ProjectVersionBuilder($project->vhost, $versionNumber);
        }

        public function getPathConfig(): PathConfig
        {
            if (!isset($this->config)) {
                $this->config = new PathConfig($this->vhost->getConfigurations(), $this->versionNumber, $this->defaultModule->pathName);
            }
            return $this->config;
        }

        public function getModules(): \Generator
        {
            if ($this->exists()) {
                $config = $this->getPathConfig();
                $folders = array_diff(scandir($this->rootPath . $config->modules), ['.', '..']);
                foreach ($folders as $moduleName) {
                    yield new ModuleBuilder(ModuleName::create($moduleName), $this);
                }
            }
        }
    }

}
