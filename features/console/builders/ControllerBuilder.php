<?php

/**
 * Description of ControllerBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
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
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->version->config->controllers . '\\' . $this->requestMethod);
            $this->path = $module->path . $module->version->config->controllers . '/' . $this->requestMethod . '/' . $controllerName . '.php';
        }

        private function createViews(): void
        {
            $viewPath = $this->module->path . $this->module->version->config->views . '/' . strtolower($this->controllerName);
            if (!is_dir($viewPath)) {
                mkdir($viewPath, LocalStorage::FILE_MODE, true);
            }
            $intext = 'Creating view: ' . Framework::HOME_FUNCTION;
            $outtext = copy(CommandContract::ASSETS . '/view.txt', $viewPath . '/' . Framework::HOME_FUNCTION . '.php') ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function createLanguage(): void
        {
            $languagePath = $this->module->path . $this->module->version->config->languages . '/';
            $languagePath .= strtolower($this->controllerName) . '/' . Framework::HOME_FUNCTION;
            if (!is_dir($languagePath)) {
                mkdir($languagePath, LocalStorage::FILE_MODE, true);
            }
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
            $service = new ServiceBuilder($this->controllerName, $this->module);
            $service->build();
            ///////////////////////////////////////////
            $entity = new EntityBuilder($this->controllerName, $this->module);
            $entity->build();
            ///////////////////////////////////////////
            if (!$this->exists()) {
                $this->createViews();
                $this->createLanguage();
                ///////////////////////////////////////////
                $search = ['{namespace}', '{controller_name}', '{service_ns}', '{fn_name}'];
                $replace = [$this->namespace, $this->controllerName, $service->namespace, Framework::HOME_FUNCTION];
                $folder = dirname($this->path);
                if (!is_dir($folder)) {
                    mkdir($folder, LocalStorage::FILE_MODE, true);
                }
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
