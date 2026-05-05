<?php

/**
 * Description of ListProjectControllersCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;

    final class ListProjectControllersCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $moduleName;

        public function __construct()
        {
            parent::__construct('list:controller', 'module_name@project_name', 'Show all available project controllers and their respective request method', 'posts@blog');
        }

        public function execute(): void
        {
            echo 'Listing all module controllers: ' . $this->projectName . PHP_EOL;
            $project = new ProjectBuilder($this->projectName);
            $module = new ModuleBuilder($this->moduleName, $project);
            if (!$module->exists()) {
                echo 'Module "' . $this->moduleName . '" does not exists.' . PHP_EOL;
                return;
            }
            $controllers = $module->getControllers();
            foreach ($controllers as $key => $controller) {
                $outtext = '[' . strtoupper($controller->requestMethod) . '] ' . $controller->controllerName;
                echo Formatter::formatSentence($key + 1, $outtext);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two argument is required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateIdentifier($values[1]);
            $this->moduleName = $values[0];
            $this->projectName = $values[1];
            return $this;
        }
    }

}
