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
    use features\test\TestRunnerInterface;
    use features\utils\Directory;
    use shani\config\PathConfig;

    final class ProjectVersionBuilder implements LightBuilderInterface
    {

        private const CONFIG_DIR = 'config';
        private const CONFIG_FILE = 'config.yml';
        private const TEST_DIR = 'tests';
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

        public function registerVersion(\Closure $progressTracker): void
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
            $this->createConfigFile($progressTracker);
        }

        private function createTestFile(\Closure $progressTracker): void
        {
            $filename = 'TestRunner';
            $testDirectory = $this->rootPath . DIRECTORY_SEPARATOR . self::TEST_DIR;
            $filepath = $testDirectory . DIRECTORY_SEPARATOR . $filename . '.php';

            if (!is_file($filepath)) {

                $testTemplate = CommandContract::ASSETS . DIRECTORY_SEPARATOR . 'test.txt';
                $search = ['{namespace}', '{test_dir}', '{class_name}'];
                $replace = [$this->namespace, self::TEST_DIR, $filename];
                $testContent = str_replace($search, $replace, file_get_contents($testTemplate));

                if (!is_dir($testDirectory)) {
                    mkdir($testDirectory, LocalStorage::FILE_MODE, true);
                }

                $outtext = file_put_contents($filepath, $testContent) !== false ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence('Creating Test class: ' . $filename, $outtext));
            }
        }

        private function createConfigFile(\Closure $progressTracker): void
        {
            if (!$this->configExists()) {
                $template = CommandContract::ASSETS . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
                $search = ['{namespace}', '{config_dir}', '{test_dir}'];
                $replace = [$this->namespace, self::CONFIG_DIR, self::TEST_DIR];
                $templateContent = str_replace($search, $replace, file_get_contents($template));

                if (!is_dir($this->vhost->metadata->hostDirectory)) {
                    mkdir($this->vhost->metadata->hostDirectory, LocalStorage::FILE_MODE, true);
                }

                $outtext = file_put_contents($this->configFilepath, $templateContent) !== false ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence('Creating configuration file: ' . basename($this->configFilepath), $outtext));
            }
        }

        private function prepareSettings(\Closure $progressTracker): void
        {
            $filename = 'Settings';
            $configDirectory = $this->rootPath . DIRECTORY_SEPARATOR . self::CONFIG_DIR;
            $filepath = $configDirectory . DIRECTORY_SEPARATOR . $filename . '.php';

            if (!is_file($filepath)) {

                $settingTemplate = CommandContract::ASSETS . DIRECTORY_SEPARATOR . 'settings.txt';
                $search = ['{namespace}', '{config_dir}', '{home_path}', '{file_name}', '{default_module}'];
                $replace = [$this->namespace, self::CONFIG_DIR, $this->defaultModule->pathName, $filename, self::DEFAULT_MODULE];
                $settingContent = str_replace($search, $replace, file_get_contents($settingTemplate));

                if (!is_dir($configDirectory)) {
                    mkdir($configDirectory, LocalStorage::FILE_MODE, true);
                }

                $outtext = file_put_contents($filepath, $settingContent) !== false ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence('Creating setting class: ' . $filename, $outtext));
            }
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if ($this->exists()) {
                throw new \RuntimeException('Project version "' . $this->versionName . '" already exists');
            }
            $this->registerVersion($progressTracker);
            $this->prepareSettings($progressTracker);
            $this->createTestFile($progressTracker);

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

        public function runTest(): bool
        {
            $filename = $this->vhost->getConfigurations()->getConfigFileName($this->versionNumber);
            $filePath = $this->vhost->metadata->hostDirectory . DIRECTORY_SEPARATOR . $filename;
            if (!is_file($filePath)) {
                throw new \RuntimeException('Test could not start because Test runner file could not be not found.');
            }
            $config = yaml_parse_file($filePath);
            if (empty($config['test'])) {
                throw new \RuntimeException('Your project version does not support test.');
            }
            $test = new $config['test']();
            if ($test instanceof TestRunnerInterface) {
                return $test->runTest()->getResult();
            }
            throw new \RuntimeException($config['test'] . ' must implements features\test\TestRunnerInterface');
        }
    }

}
