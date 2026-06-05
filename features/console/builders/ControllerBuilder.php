<?php

/**
 * Description of ControllerBuilder
 * @author goddy
 *
 * @since May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;
    use shani\launcher\Framework;

    final class ControllerBuilder implements LightBuilderInterface
    {

        private readonly string $namespace;
        private readonly string $rootPath;
        public readonly string $controllerName;
        public readonly string $requestMethod;
        private readonly ModuleBuilder $module;

        public function __construct(ModuleBuilder $module, string $requestMethod = 'GET')
        {
            $this->module = $module;
            $this->requestMethod = strtolower($requestMethod);
            $this->controllerName = ResourceName::create($module->moduleName->className, 'Controller')->longName;
            $this->namespace = str_replace('/', '\\', $module->namespace . $module->config->controllers . '\\' . $this->requestMethod);
            $path = $module->rootPath . $module->config->controllers . DIRECTORY_SEPARATOR;
            $path .= $this->requestMethod . DIRECTORY_SEPARATOR . $this->controllerName . '.php';
            $this->rootPath = $path;
        }

        private function createViews(): ?string
        {
            $viewPath = $this->module->rootPath . $this->module->config->views;
            if (is_dir($viewPath)) {
                return null;
            }
            mkdir($viewPath, LocalStorage::FILE_MODE, true);
            $destination = $viewPath . DIRECTORY_SEPARATOR . Framework::HOME_FUNCTION . '.php';
            $intext = 'Creating view: ' . Framework::HOME_FUNCTION;
            $outtext = copy(CommandContract::ASSETS . '/view.txt', $destination) ? 'Success' : 'Failed';
            return Formatter::formatSentence($intext, $outtext);
        }

        private function createLanguage(): ?string
        {
            $languagePath = $this->module->rootPath . $this->module->config->languages;
            $languagePath .= DIRECTORY_SEPARATOR . Framework::HOME_FUNCTION;
            if (is_dir($languagePath)) {
                return null;
            }
            mkdir($languagePath, LocalStorage::FILE_MODE, true);
            $intext = 'Creating language directory: ' . Framework::HOME_FUNCTION;
            $outtext = copy(CommandContract::ASSETS . '/lang.txt', $languagePath . '/en.php') ? 'Success' : 'Failed';
            return Formatter::formatSentence($intext, $outtext);
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->module->exists()) {
                $message = 'Could not create Controller class "' . $this->controllerName;
                $message .= '", module "' . $this->module->moduleName->originalValue . '" does not exists.';
                throw new \RuntimeException($message);
            }
            if (!$this->exists()) {
                $progressTracker($this->createViews());
                $progressTracker($this->createLanguage());
                ///////////////////////////////////////////
                $search = ['{namespace}', '{controller_name}', '{fn_name}', '{module_name}'];
                $replace = [$this->namespace, $this->controllerName, Framework::HOME_FUNCTION, $this->module->moduleName->className];
                $folder = dirname($this->rootPath);
                if (!is_dir($folder)) {
                    mkdir($folder, LocalStorage::FILE_MODE, true);
                }
                $filecontent = file_get_contents(CommandContract::ASSETS . '/controller.txt');
                ///////////////////////////////////////////
                $intext = 'Creating controller: ' . $this->controllerName;
                $outtext = file_put_contents($this->rootPath, str_replace($search, $replace, $filecontent)) !== false ? 'Success' : 'Failed';
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
