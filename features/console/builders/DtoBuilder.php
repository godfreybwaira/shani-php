<?php

/**
 * Description of DtoBuilder
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

    final class DtoBuilder implements LightBuilderInterface
    {

        private readonly string $namespace;
        private readonly string $rootPath;
        private readonly EntityBuilder $entity;
        public readonly string $dtoName;

        public function __construct(EntityBuilder $entity, string $dtoName = null)
        {
            $this->entity = $entity;
            $this->dtoName = ResourceName::create($dtoName ?? $entity->module->moduleName->className, 'Dto')->longName;
            $this->namespace = str_replace('/', '\\', $entity->module->namespace . $entity->module->config->dto);
            $this->rootPath = $entity->module->rootPath . $entity->module->config->dto . DIRECTORY_SEPARATOR . $this->dtoName . '.php';
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->entity->module->exists()) {
                throw new \RuntimeException('Could not create DTO "' . $this->dtoName . '", module "' . $this->entity->module->moduleName . '" does not exists.');
            }
            if ($this->exists()) {
                throw new \RuntimeException('Could not create DTO "' . $this->dtoName . '", it exists already.');
            }
            $search = ['{namespace}', '{dto_name}', '{entity_name}', '{entity_ns}'];
            $replace = [$this->namespace, $this->dtoName, $this->entity->entityName, $this->entity->namespace];
            $folder = dirname($this->rootPath);
            if (!is_dir($folder)) {
                mkdir($folder, LocalStorage::FILE_MODE, true);
            }
            $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/dto.txt'));
            $outtext = file_put_contents($this->rootPath, $content) !== false ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence('Creating DTO: ' . $this->dtoName, $outtext));
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
