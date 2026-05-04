<?php

/**
 * Description of ModuleBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;

    final class ModuleBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        public readonly ProjectBuilder $project;
        public readonly string $moduleName;

        public function __construct(string $moduleName, ProjectBuilder $project)
        {
            $this->moduleName = $moduleName;
            $this->project = $project;
            $this->namespace = str_replace('/', '\\', $project->namespace . $project->config->modules . '\\' . $moduleName);
            $this->path = $project->config->root . $project->config->modules . '/' . $moduleName;
        }

        public function getControllers(): array
        {
            $controllers = [];
            if (!$this->exists()) {
                return $controllers;
            }
            $controllerPath = $this->path . $this->project->config->controllers;
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
                $folders = array_diff(scandir($this->path . $this->project->config->services), ['.', '..']);
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
                $folders = array_diff(scandir($this->path . $this->project->config->entities), ['.', '..']);
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
                $folders = array_diff(scandir($this->path . $this->project->config->dto), ['.', '..']);
                foreach ($folders as $dtoName) {
                    $dtos[] = new DtoBuilder(basename($dtoName, '.php'), null, $this, '');
                }
            }
            return $dtos;
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->project->exists()) {
                echo 'Could not create module "' . $this->moduleName . '", project "' . $this->project->projectName . '" does not exists.' . PHP_EOL;
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

        public function locateEntities(): void
        {
            $entities = $this->getEntities();
            foreach ($entities as $entity) {
                echo $entity->exists() ? $entity->path . PHP_EOL : null;
            }
        }

        public function locateServices(): void
        {
            $services = $this->getServices();
            foreach ($services as $service) {
                echo $service->exists() ? $service->path . PHP_EOL : null;
            }
        }

        public function locateControllers(): void
        {
            $controllers = $this->getControllers();
            foreach ($controllers as $controller) {
                echo $controller->exists() ? $controller->path . PHP_EOL : null;
            }
        }

        public function locateDto(): void
        {
            $dtos = $this->getDtos();
            foreach ($dtos as $dto) {
                echo $dto->exists() ? $dto->path . PHP_EOL : null;
            }
        }
    }

}
