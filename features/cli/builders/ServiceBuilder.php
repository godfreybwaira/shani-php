<?php

/**
 * Description of ServiceBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\Create;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;

    final class ServiceBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        private readonly string $serviceName;
        private readonly ModuleBuilder $module;

        public function __construct(string $serviceName, ModuleBuilder $module)
        {
            $this->serviceName = $serviceName;
            $this->module = $module;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->project->config->services);
            $this->path = $module->path . $module->project->config->services . '/' . $serviceName . '.php';
        }

        #[\Override]
        public function build(): self
        {

            if (!$this->module->exists()) {
                echo 'Could not create service "' . $this->serviceName . '", module "' . $this->module->moduleName . '" not exists.';
                return $this;
            }
            if ($this->exists()) {
                return $this;
            }
            ///////////////////////////////////////////
            $search = ['{namespace}', '{class_name}'];
            $replace = [$this->namespace, $this->serviceName];
            mkdir(dirname($this->path), LocalStorage::FILE_MODE, true);
            $content = str_replace($search, $replace, file_get_contents(Create::ASSETS . '/class.txt'));
            ///////////////////////////////////////////
            $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
            $intext = 'Creating service: ' . $this->serviceName;
            echo Formatter::formatSentence($intext, $outtext);
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->path);
        }
    }

}
