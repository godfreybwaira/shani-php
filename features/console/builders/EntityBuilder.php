<?php

/**
 * Description of EntityBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 7:56:24 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;

    final class EntityBuilder implements LightBuilderInterface
    {

        private readonly string $rootPath;
        public readonly string $entityName;
        public readonly string $namespace;
        public readonly ModuleBuilder $module;

        public function __construct(ModuleBuilder $module, string $entityName = null)
        {
            $this->module = $module;
            $this->entityName = ResourceName::create($entityName ?? $module->moduleName->className, 'Entity')->longName;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->config->entities);
            $this->rootPath = $module->rootPath . $module->config->entities . '/' . $this->entityName . '.php';
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->module->exists()) {
                throw new \RuntimeException('Could not create Entity "' . $this->entityName . '", module "' . $this->module->moduleName . '" does not exists.');
            }
            $dto = new DtoBuilder($this);
            $dto->build($progressTracker);
            ///////////////////////////////////////////
            if (!$this->exists()) {
                $search = ['{namespace}', '{class_name}'];
                $replace = [$this->namespace, $this->entityName];
                $folder = dirname($this->rootPath);
                if (!is_dir($folder)) {
                    mkdir($folder, LocalStorage::FILE_MODE, true);
                }
                $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/entity.txt'));
                if (!is_dir($this->module->rootPath . $this->module->config->values)) {
                    mkdir($this->module->rootPath . $this->module->config->values, LocalStorage::FILE_MODE, true);
                }
                $intext = 'Creating entity: ' . $this->entityName;
                $outtext = file_put_contents($this->rootPath, $content) !== false ? 'Success' : 'Failed';
                $progressTracker(Formatter::formatSentence($intext, $outtext));
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
