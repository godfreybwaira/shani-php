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

        public function getServices(): array
        {
            $services = [];
            if ($this->exists()) {
                $folders = array_diff(scandir($this->path . $this->project->config->services), ['.', '..']);
                foreach ($folders as $serviceName) {
                    $services[] = new ServiceBuilder($serviceName, $this);
                }
            }
            return $this;
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->project->exists()) {
                echo 'Could not create module "' . $this->moduleName . '", project "' . $this->project->projectName . '" not exists.' . PHP_EOL;
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
    }

}
