<?php

/**
 * Description of DtoBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 7:56:24 PM
 */

namespace features\cli\builders {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;

    final class DtoBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        private readonly string $dtoName;
        private readonly string $longDtoName;
        private readonly string $entityNamespace;
        private readonly ModuleBuilder $module;

        public function __construct(string $dtoName, string $entityNamespace, ModuleBuilder $module)
        {
            $this->module = $module;
            $this->dtoName = $dtoName;
            $this->longDtoName = $dtoName . 'Dto';
            $this->entityNamespace = $entityNamespace;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->project->config->dto);
            $this->path = $module->path . $module->project->config->dto . '/' . $this->longDtoName . '.php';
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->module->exists()) {
                echo 'Could not create DTO "' . $this->longDtoName . '", module "' . $this->module->moduleName . '" does not exists.' . PHP_EOL;
                return $this;
            }
            if (!$this->exists()) {
                ///////////////////////////////////////////
                $search = ['{namespace}', '{class_name}', '{entity_ns}'];
                $replace = [$this->namespace, $this->dtoName, $this->entityNamespace];
                mkdir(dirname($this->path), LocalStorage::FILE_MODE, true);
                $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/dto.txt'));
                $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
                $intext = 'Creating DTO: ' . $this->longDtoName;
                echo Formatter::formatSentence($intext, $outtext);
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->path);
        }
    }

}
