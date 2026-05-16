<?php

/**
 * Command to create a new project.
 *
 * This command generates a new main project with its associated host.
 * It can be executed interactively (via console prompts) or by passing
 * arguments directly in the format "project@hostname".
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;

    final class CreateProjectCommand extends CommandContract
    {

        /**
         * The name of the project to create.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The hostname associated with the project.
         *
         * @var string
         */
        private readonly string $hostName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:project', 'project_name@hostname', 'Create a new main project', 'demo@localhost');
        }

        /**
         * Executes the project creation operation.
         *
         * Uses the {@see ProjectBuilder} to build the project and logs
         * the result in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $project = ProjectBuilder::fromMetaData($this->projectName, $this->hostName);
            $project->build(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to enter a project
         *   name and host name interactively.
         * - If arguments are provided, expects the format "project@hostname".
         *   Splits the string into project name and host name.
         *
         * @param string ...$args The command arguments (project@hostname).
         *
         * @return string|null A string containing "project@hostname" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $name = ConsoleIO::read('Enter the project name:', $this->validIdentifier);
                $this->projectName = ModuleName::create($name)->directoryName;
                $this->hostName = ConsoleIO::read('Enter the host name:', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ModuleName::create($values[0])->directoryName;
                $this->hostName = HostName::create($values[1]);
            }
            return $this->projectName . self::SEPARATOR . $this->hostName;
        }
    }

}
