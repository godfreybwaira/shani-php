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
    use shani\config\PathConfig;
    use shani\launcher\Framework;

    final class ProjectVersionBuilder implements LightBuilderInterface
    {

        public const CONFIG_DIR = 'config';
        private const DEFAULT_MODULE = 'users';
        private const DEFAULT_CONTROLLER = 'Account';

        public readonly string $path;
        public readonly PathConfig $config;
        public readonly string $versionNumber;
        public readonly string $versionName;
        public readonly ProjectBuilder $project;
        public readonly string $namespace;

        public function __construct(ProjectBuilder $project, string $versionNumber, string $versionName = null)
        {
            $this->project = $project;
            $this->versionNumber = $versionNumber;
            $this->versionName = $versionName ?? $versionNumber;
            $this->path = $project->path . '/' . $this->versionName;
            $this->namespace = str_replace('/', '\\', substr($this->path, strlen(SHANI_SERVER_ROOT) + 1));
            $this->config = new PathConfig($this->path, self::getHomePath());
        }

        private static function getHomePath(): string
        {
            return strtolower('/' . self::DEFAULT_MODULE . '/0/' . self::DEFAULT_CONTROLLER . '/1/' . Framework::HOME_FUNCTION);
        }

        private function prepareSettings(): void
        {
            $subpath = substr($this->path, strlen(Framework::DIR_APPS) + 1);
            $filename = 'Settings';
            $template = CommandContract::ASSETS . '/settings.txt';
            $configPath = $this->path . '/' . self::CONFIG_DIR;
            $search = ['{namespace}', '{config_dir}', '{version_subpath}', '{home_path}', '{file_name}'];
            $replace = [$this->namespace, self::CONFIG_DIR, $subpath, $this->config->homePath, $filename];
            $content = str_replace($search, $replace, file_get_contents($template));
            mkdir($configPath, LocalStorage::FILE_MODE, true);
            ///////////////////////////////////////////
            $outtext = file_put_contents($configPath . '/' . $filename . '.php', $content) !== false ? 'Success' : 'Failed';
            $intext = 'Creating setting class: ' . $filename;
            echo Formatter::formatSentence($intext, $outtext);
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
                $this->project->vhost->addVersion($this);
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
            return is_dir($this->path);
        }
    }

}
