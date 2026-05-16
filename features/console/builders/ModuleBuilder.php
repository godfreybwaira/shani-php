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
    use features\documentation\scanners\Modules;
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
            $this->config = $version->getPathConfig();
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
                $text .= $this->version->versionNumber . '" does not exists.';
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

        public function getControllers(): \Generator
        {
            $controllerPath = $this->rootPath . $this->config->controllers;
            if (!is_dir($controllerPath)) {
                throw new \InvalidArgumentException('No controller available for "' . $this->moduleName->originalValue . '" module');
            }
            $folders = array_diff(scandir($controllerPath), ['.', '..']);
            foreach ($folders as $method) {
                $files = array_diff(scandir($controllerPath . DIRECTORY_SEPARATOR . $method), ['.', '..']);
                foreach ($files as $v) {
                    yield new ControllerBuilder($this, $method);
                }
            }
        }

        public function getEntities(): \Generator
        {
            $folders = array_diff(scandir($this->rootPath . $this->config->entities), ['.', '..']);
            if (empty($folders)) {
                throw new \InvalidArgumentException('No entity available for "' . $this->moduleName->originalValue . '" module');
            }
            foreach ($folders as $entityName) {
                yield new EntityBuilder($this, basename($entityName, '.php'));
            }
        }

        public function getDtos(): \Generator
        {
            $folders = array_diff(scandir($this->rootPath . $this->config->dto), ['.', '..']);
            if (empty($folders)) {
                throw new \InvalidArgumentException('No DTO available for "' . $this->moduleName->originalValue . '" module');
            }
            foreach ($folders as $dtoName) {
                yield new DtoBuilder(new EntityBuilder($this), basename($dtoName, '.php'));
            }
        }

        public function getServices(): \Generator
        {
            $folders = array_diff(scandir($this->rootPath . $this->config->services), ['.', '..']);
            if (empty($folders)) {
                throw new \InvalidArgumentException('No Service class available for "' . $this->moduleName->originalValue . '" module');
            }
            foreach ($folders as $serviceName) {
                yield new ServiceBuilder($this, basename($serviceName, '.php'));
            }
        }

        public static function fromModuleName(ModuleName $moduleName, string $projectName, string $versionNumber): ModuleBuilder
        {
            $version = ProjectVersionBuilder::fromProjectName($projectName, $versionNumber);
            return new ModuleBuilder($moduleName, $version);
        }

        public function getRoutes(): \Generator
        {
            if (!$this->exists()) {
                throw new \InvalidArgumentException('Module "' . $this->moduleName->originalValue . '" does not exists.');
            }
            $controllerPath = $this->rootPath . $this->config->controllers;
            $module = new Modules($this->moduleName->directoryName, $controllerPath);
            $classLists = $module->getClassList();
            $host = 'http://' . $this->version->vhost->metadata->hostName;
            foreach ($classLists as $controller) {
                $requestMethod = strtoupper($controller->requestMethod);
                $endpoints = $controller->getEndpoints();
                foreach ($endpoints as $endpoint) {
                    yield $host . $endpoint->target => $requestMethod;
                }
            }
        }
    }

}
