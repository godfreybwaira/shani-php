<?php

/**
 * Description of CreateControllerCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\controller {

    use features\console\builders\ControllerBuilder;
    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateControllerCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $projectVersion;
        private readonly string $moduleName;
        private readonly string $controllerName;

        public function __construct()
        {
            parent::__construct('create:controller', 'controller_name@module_name@version_number@project_name', 'Create a new project controller, it\'s associated service, dto, entity, view and language file', 'Review@posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $module = new ModuleBuilder($this->moduleName, $version);
            $controller = new ControllerBuilder($this->controllerName, $module);
            $controller->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->controllerName = ConsoleIO::input('What is the controller name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('Atleast four arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->validateIdentifier($values[2]);
                $this->validateIdentifier($values[3]);
                $this->controllerName = $values[0];
                $this->moduleName = $values[1];
                $this->projectVersion = $values[2];
                $this->projectName = $values[3];
            }
            return $this;
        }
    }

}
