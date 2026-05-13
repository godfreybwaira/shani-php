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

    final class ProjectVersionBuilder implements LightBuilderInterface
    {

        private const CONFIG_DIR = 'config';
        private const CONFIG_FILE = 'config.yml';
        public const DEFAULT_MODULE = 'users';

        public readonly VirtualHostBuilder $vhost;
        public readonly ModuleName $defaultModule;
        public readonly string $namespace;
        public readonly string $versionNumber;
        private readonly string $versionName;
        private readonly string $rootPath;

        public function __construct(VirtualHostBuilder $vhost, string $versionNumber, string $versionName = null)
        {
            $this->vhost = $vhost;
            $this->versionNumber = $versionNumber;
            $this->versionName = $versionName ?? $versionNumber;
            $this->rootPath = $vhost->metadata->projectDirectory . DIRECTORY_SEPARATOR . $versionNumber;
            $this->namespace = str_replace('/', '\\', trim(substr($this->rootPath, strlen(SHANI_SERVER_ROOT)), DIRECTORY_SEPARATOR));
            $this->defaultModule = ModuleName::create('users');
        }

        private function registerVersion(): string
        {
            $name = $this->versionName ?? $this->versionNumber;
            $configFile = $this->versionNumber . '-' . self::CONFIG_FILE;
            $filepath = $this->vhost->metadata->hostDirectory . DIRECTORY_SEPARATOR . $configFile;
            if (is_file($filepath)) {
                throw new \RuntimeException('Project version "' . $this->versionName . '" already registered.');
            }

            $versionTemplate = file_get_contents(CommandContract::ASSETS . '/version.yml');
            $search = ['{version_number}', '{version_name}', '{config_file}'];
            $replace = [$this->versionNumber, $name, $configFile];
            $versionContent = str_replace($search, $replace, $versionTemplate);

            $placeholder = '####v1#';
            $hostTemplate = file_get_contents($this->vhost->metadata->hostPath);
            $hostContent = str_replace($placeholder, $versionContent . PHP_EOL . $placeholder, $hostTemplate);

            if (file_put_contents($this->vhost->metadata->hostPath, $hostContent) !== false) {
                return $filepath;
            }
            throw new \RuntimeException('Project version "' . $name . '" could not be created.');
        }

        private function createConfigFile(string $filepath): string
        {
            $template = CommandContract::ASSETS . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$this->namespace, self::CONFIG_DIR];
            $templateContent = str_replace($search, $replace, file_get_contents($template));

            $outtext = file_put_contents($filepath, $templateContent) !== false ? 'Success' : 'Failed';
            return Formatter::formatSentence('Creating configuration file: ' . basename($filepath), $outtext);
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
            $filepath = $this->registerVersion();
            $progressTracker($this->createConfigFile($filepath));
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
    }

}
