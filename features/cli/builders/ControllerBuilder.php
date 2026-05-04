<?php

/**
 * Description of ControllerBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;
    use shani\launcher\Framework;

    final class ControllerBuilder implements LightBuilderInterface
    {

        public readonly string $namespace;
        public readonly string $path;
        public readonly string $controllerName;
        public readonly string $requestMethod;
        private readonly ModuleBuilder $module;

        public function __construct(string $controllerName, ModuleBuilder $module, string $requestMethod = 'GET')
        {
            $this->controllerName = $controllerName;
            $this->requestMethod = strtolower($requestMethod);
            $this->module = $module;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->project->config->controllers . '\\' . $this->requestMethod);
            $this->path = $module->path . $module->project->config->controllers . '/' . $this->requestMethod . '/' . $controllerName . '.php';
        }

        private function createViews(): void
        {
            $viewPath = $this->module->path . $this->module->project->config->views . '/' . strtolower($this->controllerName);
            mkdir($viewPath, LocalStorage::FILE_MODE, true);
            $intext = 'Creating view: ' . Framework::HOME_FUNCTION;
            $outtext = copy(CommandContract::ASSETS . '/view.txt', $viewPath . '/' . Framework::HOME_FUNCTION . '.php') ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function createLanguage(): void
        {
            $languagePath = $this->module->path . $this->module->project->config->languages . '/';
            $languagePath .= strtolower($this->controllerName) . '/' . Framework::HOME_FUNCTION;
            mkdir($languagePath, LocalStorage::FILE_MODE, true);
            ///////////////////////////////////////////
            $intext = 'Creating language directory: ' . Framework::HOME_FUNCTION;
            $outtext = copy(CommandContract::ASSETS . '/lang.txt', $languagePath . '/en.php') ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->module->exists()) {
                echo 'Could not create controller "' . $this->controllerName . '", module "' . $this->module->moduleName . '" does not exists.' . PHP_EOL;
                return $this;
            }
            $entity = new EntityBuilder($this->controllerName . 'Entity', $this->module);
            $entity->build();
            $dto = new DtoBuilder($this->controllerName, $entity->namespace, $this->module);
            $dto->build();
            if (!$this->exists()) {
                $this->createViews();
                $this->createLanguage();
                ///////////////////////////////////////////
                $search = ['{namespace}', '{controller_name}', '{fn_name}'];
                $replace = [$this->namespace, $this->controllerName, Framework::HOME_FUNCTION];
                mkdir(dirname($this->path), LocalStorage::FILE_MODE, true);
                $filecontent = file_get_contents(CommandContract::ASSETS . '/controller.txt');
                ///////////////////////////////////////////
                $intext = 'Creating controller: ' . $this->controllerName;
                $outtext = file_put_contents($this->path, str_replace($search, $replace, $filecontent)) !== false ? 'Success' : 'Failed';
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
