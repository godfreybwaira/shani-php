<?php

/**
 * Description of DtoBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 7:56:24 PM
 */

namespace features\cli\builders {

    use features\cli\Create;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;

    final class DtoBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        private readonly string $dtoName;
        private readonly ModuleBuilder $module;

        public function __construct(string $dtoName, ModuleBuilder $module)
        {
            $this->dtoName = $dtoName;
            $this->module = $module;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->project->config->dto);
            $this->path = $module->path . $module->project->config->dto . '/' . $dtoName . '.php';
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->module->exists()) {
                echo 'Could not create DTO "' . $this->dtoName . '", module "' . $this->module->moduleName . '" not exists.' . PHP_EOL;
                return $this;
            }
            if (!$this->exists()) {
                ///////////////////////////////////////////
                $search = ['{namespace}', '{class_name}'];
                $replace = [$this->namespace, $this->dtoName];
                mkdir(dirname($this->path), LocalStorage::FILE_MODE, true);
                $content = str_replace($search, $replace, file_get_contents(Create::ASSETS . '/dto.txt'));
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
    }

}
