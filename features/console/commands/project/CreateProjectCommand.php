<?php

/**
 * Description of CreateProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateProjectCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('create:project', 'project_name@hostname', 'Create a new main project. You can add versions to main project', 'demo@localhost');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName, $this->hostname);
            $project->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->hostname = ConsoleIO::input('What is the host name?', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateHostName($values[1]);
                $this->projectName = $values[0];
                $this->hostname = $values[1];
            }
            return $this;
        }
    }

}
