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
    use features\storage\LocalStorage;
    use shani\launcher\ShaniUtils;

    final class DtoBuilder implements LightBuilderInterface
    {

        private const SUFFIX = 'Dto';

        public readonly string $namespace;
        public readonly string $path;
        public readonly string $dtoName;
        private readonly string $entityNamespace;
        private readonly ModuleBuilder $module;

        public function __construct(string $dtoName, ModuleBuilder $module, string $entityNamespace)
        {
            $this->module = $module;
            $this->entityNamespace = $entityNamespace;
            $this->dtoName = ShaniUtils::trimSuffix($dtoName, self::SUFFIX) . self::SUFFIX;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->version->config->dto);
            $this->path = $module->path . $module->version->config->dto . '/' . $this->dtoName . '.php';
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->module->exists()) {
                echo 'Could not create DTO "' . $this->dtoName . '", module "' . $this->module->moduleName . '" does not exists.' . PHP_EOL;
                return $this;
            }
            if (!$this->exists()) {
                $dtoName = ShaniUtils::trimSuffix($this->dtoName, self::SUFFIX);
                $search = ['{namespace}', '{class_name}', '{entity_ns}'];
                $replace = [$this->namespace, $dtoName, $this->entityNamespace];
                $folder = dirname($this->path);
                if (!is_dir($folder)) {
                    mkdir($folder, LocalStorage::FILE_MODE, true);
                }
                $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/dto.txt'));
                $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
                $intext = 'Creating DTO: ' . $this->dtoName;
                echo Formatter::formatSentence($intext, $outtext);
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->path);
        }

        public static function fromName(string $dtoName, ModuleBuilder $module): DtoBuilder
        {
            $entityName = ShaniUtils::trimSuffix($dtoName, self::SUFFIX);
            $entity = new EntityBuilder($entityName, $module);
            return new DtoBuilder($dtoName, $module, $entity->namespace);
        }

        public function locate(): void
        {
            echo $this->exists() ? $this->path : null;
        }
    }

}
