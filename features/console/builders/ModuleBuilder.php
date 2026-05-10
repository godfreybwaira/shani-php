<?php

/**
 * Description of ModuleBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\helpers\Formatter;
    use features\storage\LocalStorage;

    final class ModuleBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        public readonly ProjectVersionBuilder $version;
        public readonly string $moduleName;

        public function __construct(string $moduleName, ProjectVersionBuilder $version)
        {
            $this->version = $version;
            $this->moduleName = $moduleName;
            $this->namespace = str_replace('/', '\\', $version->namespace . $version->config->modules . '\\' . $moduleName);
            $this->path = $version->config->root . $version->config->modules . '/' . $moduleName;
        }

        public function getControllers(): array
        {
            $controllers = [];
            if (!$this->exists()) {
                return $controllers;
            }
            $controllerPath = $this->path . $this->version->config->controllers;
            $folders = array_diff(scandir($controllerPath), ['.', '..']);
            foreach ($folders as $method) {
                $files = array_diff(scandir($controllerPath . '/' . $method), ['.', '..']);
                foreach ($files as $controllerName) {
                    $controllers[] = new ControllerBuilder(basename($controllerName, '.php'), $this, $method);
                }
            }
            return $controllers;
        }

        public function locate(): void
        {
            echo $this->exists() ? $this->path : null;
        }

        public function getServices(): array
        {
            $services = [];
            if ($this->exists()) {
                $folders = array_diff(scandir($this->path . $this->version->config->services), ['.', '..']);
                foreach ($folders as $serviceName) {
                    $services[] = new ServiceBuilder(basename($serviceName, '.php'), $this);
                }
            }
            return $services;
        }

        public function getEntities(): array
        {
            $entities = [];
            if ($this->exists()) {
                $folders = array_diff(scandir($this->path . $this->version->config->entities), ['.', '..']);
                foreach ($folders as $entityName) {
                    $entities[] = new EntityBuilder(basename($entityName, '.php'), $this);
                }
            }
            return $entities;
        }

        public function getDtos(): array
        {
            $dtos = [];
            if ($this->exists()) {
                $folders = array_diff(scandir($this->path . $this->version->config->dto), ['.', '..']);
                foreach ($folders as $dtoName) {
                    $dtos[] = new DtoBuilder(basename($dtoName, '.php'), $this, '');
                }
            }
            return $dtos;
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->version->exists()) {
                echo 'Could not create module "' . $this->moduleName . '", project version "';
                echo $this->version->versionName . '" does not exists.' . PHP_EOL;
                return $this;
            }
            if (!$this->exists()) {
                $intext = 'Creating module "' . $this->moduleName . '"';
                $outtext = mkdir($this->path, LocalStorage::FILE_MODE, true) ? 'Success' : 'Failed';
                echo Formatter::formatSentence($intext, $outtext);
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->path);
        }

        public function locateControllers(): void
        {
            $controllers = $this->getControllers();
            foreach ($controllers as $controller) {
                echo $controller->exists() ? $controller->path . PHP_EOL : null;
            }
        }
    }

}
