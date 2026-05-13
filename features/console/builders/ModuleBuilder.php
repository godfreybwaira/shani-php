<?php

/**
 * Description of ModuleBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;
    use shani\config\PathConfig;

    final class ModuleBuilder implements LightBuilderInterface
    {

        private readonly ProjectVersionBuilder $version;
        public readonly string $namespace;
        public readonly ModuleName $moduleName;
        public readonly string $rootPath;
        public readonly PathConfig $config;

        public function __construct(ModuleName $moduleName, ProjectVersionBuilder $version)
        {
            $this->version = $version;
            $this->moduleName = $moduleName;
            $this->config = new PathConfig($version->vhost->getConfigurations(), $version->versionNumber, $version->defaultModule->pathName);
            $this->namespace = str_replace('/', '\\', $version->namespace . $this->config->modules . '\\' . $moduleName->directoryName);
            $this->rootPath = $this->config->root . $this->config->modules . DIRECTORY_SEPARATOR . $moduleName->directoryName;
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->rootPath);
            }
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->version->exists()) {
                $text = 'Could not create module "' . $this->moduleName->originalValue . '", project version "';
                $text .= $this->version->versionName . '" does not exists.';
                throw new \RuntimeException($text);
            }
            if (!$this->exists()) {
                $intext = 'Creating module directory "' . $this->moduleName->directoryName . '"';
                $outtext = mkdir($this->rootPath, LocalStorage::FILE_MODE, true) ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence($intext, $outtext));
            }
            $controller = new ControllerBuilder($this);
            $controller->build($progressTracker);

            $service = new ServiceBuilder($this);
            $service->build($progressTracker);

            $entity = new EntityBuilder($this);
            $entity->build($progressTracker);

            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->rootPath);
        }
    }

}
