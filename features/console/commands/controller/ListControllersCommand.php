<?php

/**
 * Description of ListProjectControllersCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\controller {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;

    final class ListControllersCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $projectVersion;
        private readonly string $moduleName;

        public function __construct()
        {
            parent::__construct('list:controller', 'module_name@version_number@project_name', 'Show all available project controllers and their respective request method', 'posts@v1@blog');
        }

        public function execute(): void
        {
            echo 'Listing all module controllers: ' . $this->projectName . PHP_EOL;
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $module = new ModuleBuilder($this->moduleName, $version);
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
            if (empty($args)) {
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three argument is required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                self::validateIdentifier($values[2]);
                $this->moduleName = $values[0];
                $this->projectVersion = $values[1];
                $this->projectName = $values[2];
            }
            return $this;
        }
    }

}
