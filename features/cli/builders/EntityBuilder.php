<?php

/**
 * Description of EntityBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 7:56:24 PM
 */

namespace features\cli\builders {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;

    final class EntityBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        private readonly string $entityName;
        private readonly ModuleBuilder $module;

        public function __construct(string $entityName, ModuleBuilder $module)
        {
            $this->entityName = $entityName;
            $this->module = $module;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->project->config->entities);
            $this->path = $module->path . $module->project->config->entities . '/' . $entityName . '.php';
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->module->exists()) {
                echo 'Could not create entity "' . $this->entityName . '", module "' . $this->module->moduleName . '" does not exists.';
                return $this;
            }
            if (!$this->exists()) {
                $search = ['{namespace}', '{class_name}'];
                $replace = [$this->namespace, $this->entityName];
                mkdir(dirname($this->path), LocalStorage::FILE_MODE, true);
                $content = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/entity.txt'));
                mkdir($this->module->path . $this->module->project->config->enums, LocalStorage::FILE_MODE, true);
                ///////////////////////////////////////////
                $intext = 'Creating entity: ' . $this->entityName;
                $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
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
