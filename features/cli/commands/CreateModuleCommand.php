<?php

/**
 * Description of CreateModuleCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\ProjectBuilder;
    use features\cli\CommandContract;

    final class CreateModuleCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $moduleName;

        public function __construct()
        {
            parent::__construct('create:module', 'module_name@project_name', 'Creating a new project module', 'posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName, $this->moduleName);
            $project->build();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateIdentifier($values[1]);
            $this->moduleName = $values[0];
            $this->projectName = $values[1];
            return $this;
        }
    }

}
