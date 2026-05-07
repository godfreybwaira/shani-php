<?php

/**
 * Description of CreateModuleCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateModuleCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $moduleName;

        public function __construct()
        {
            parent::__construct('create:module', 'module_name@project_name', 'Create a new project module', 'posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $module = new ModuleBuilder($this->moduleName, $project);
            $module->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->moduleName = $values[0];
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
