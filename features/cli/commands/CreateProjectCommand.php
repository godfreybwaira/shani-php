<?php

/**
 * Description of CreateProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\ProjectBuilder;
    use features\cli\CommandContract;

    final class CreateProjectCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('create:project', 'project_name@hostname', 'Creating a new project', 'demo@localhost');
        }

        public function execute(): void
        {
            $moduleName = 'users';
            $controllerName = 'Account';
            $project = new ProjectBuilder($this->projectName, $moduleName, $controllerName);
            $project->setHostName($this->hostname)->build();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateHostName($values[1]);
            $this->projectName = $values[0];
            $this->hostname = $values[1];
            return $this;
        }
    }

}
