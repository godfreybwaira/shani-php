<?php

/**
 * Description of ServiceBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;

    final class ServiceBuilder implements LightBuilderInterface
    {

        private readonly string $namespace;
        private readonly string $rootPath;
        private readonly string $serviceName;
        private readonly ModuleBuilder $module;

        public function __construct(ModuleBuilder $module, string $serviceName = null)
        {
            $this->module = $module;
            $this->serviceName = ResourceName::create($serviceName ?? $module->moduleName->className, 'Service')->longName;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->config->services);
            $this->rootPath = $module->rootPath . $module->config->services . DIRECTORY_SEPARATOR . $this->serviceName . '.php';
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->module->exists()) {
                throw new \RuntimeException('Could not create Service class "' . $this->serviceName . '", module "' . $this->module->moduleName . '" does not exists.');
            }
            if (!$this->exists()) {
                $search = ['{namespace}', '{class_name}'];
                $replace = [$this->namespace, $this->serviceName];
                $folder = dirname($this->rootPath);
                if (!is_dir($folder)) {
                    mkdir($folder, LocalStorage::FILE_MODE, true);
                }
                $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/class.txt'));
                $outtext = file_put_contents($this->rootPath, $content) !== false ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence('Creating service: ' . $this->serviceName, $outtext));
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->rootPath);
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->rootPath);
            }
        }
    }

}
