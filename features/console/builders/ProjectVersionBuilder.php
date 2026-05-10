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
    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\config\PathConfig;
    use shani\launcher\Framework;

    final class ProjectVersionBuilder implements LightBuilderInterface
    {

        public const CONFIG_DIR = 'config';
        private const DEFAULT_MODULE = 'users';
        private const DEFAULT_CONTROLLER = 'Account';

        public readonly PathConfig $config;
        public readonly string $namespace;
        private readonly VirtualHostBuilder $vhost;
        public readonly string $versionNumber;
        private readonly string $versionName;

        public function __construct(VirtualHostBuilder $vhost, string $versionNumber, string $versionName = null)
        {
            $this->vhost = $vhost;
            $this->versionNumber = $versionNumber;
            $this->versionName = $versionName ?? $versionNumber;
            $mapper = VirtualHostBuilder::getConfigurations($vhost->path);
            $this->config = new PathConfig($mapper, $this->versionNumber, self::getHomePath());
            $this->namespace = str_replace('/', '\\', substr($this->config->root, strlen(SHANI_SERVER_ROOT) + 1));
        }

        private static function getHomePath(): string
        {
            return strtolower('/' . self::DEFAULT_MODULE . '/0/' . self::DEFAULT_CONTROLLER . '/1/' . Framework::HOME_FUNCTION);
        }

        private function prepareSettings(): void
        {
            $filename = 'Settings';
            $template = CommandContract::ASSETS . '/settings.txt';
            $configPath = $this->config->root . '/' . self::CONFIG_DIR;
            $search = ['{namespace}', '{config_dir}', '{home_path}', '{file_name}'];
            $replace = [$this->namespace, self::CONFIG_DIR, $this->config->homePath, $filename];
            $content = str_replace($search, $replace, file_get_contents($template));
            mkdir($configPath, LocalStorage::FILE_MODE, true);
            ///////////////////////////////////////////
            $outtext = file_put_contents($configPath . '/' . $filename . '.php', $content) !== false ? 'Success' : 'Failed';
            $intext = 'Creating setting class: ' . $filename;
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function createConfigFile(): void
        {
            $file = VirtualHostBuilder::getConfigFilename($this->versionNumber, $this->versionName);
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$this->namespace, ProjectVersionBuilder::CONFIG_DIR];
            $template = CommandContract::ASSETS . '/' . VirtualHostBuilder::CONFIG_FILE;
            $content = str_replace($search, $replace, file_get_contents($template));
            $outtext = file_put_contents($this->vhost->directory . '/' . $file, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence('Creating configuration file: ' . $file, $outtext);
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

        #[\Override]
        public function build(): self
        {
            if ($this->exists()) {
                echo Formatter::formatSentence('Project version "' . $this->versionName . '" already exists', 'Failed');
            } else {
                $this->createConfigFile();
                $this->prepareSettings();
                $module = new ModuleBuilder(self::DEFAULT_MODULE, $this);
                $module->build();
                $controller = new ControllerBuilder(self::DEFAULT_CONTROLLER, $module);
                $controller->build();
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->config->root);
        }

        public function delete(): void
        {
            $intext = 'Deleting project version "' . $this->versionNumber . '"';
            $outtext = $this->exists() && Directory::delete($this->config->root) ? 'Success' : 'Failed';
//            $this->vhost->directory
            echo Formatter::formatSentence($intext, $outtext);
        }
    }

}
