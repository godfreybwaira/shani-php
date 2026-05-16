<?php

/**
 * Command to create a new virtual host.
 *
 * This command creates a new virtual host for an existing project
 * that does not already have one. If the project already has a host,
 * the command prevents duplication and suggests creating an alias instead.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class CreateVhostCommand extends CommandContract
    {

        /**
         * The name of the project to associate with the virtual host.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The hostname of the virtual host to create.
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
            parent::__construct(
                    $registry,
                    'create:vhost',
                    'project_name@hostname',
                    'Create new virtual host to an existing project with no virtual host',
                    'demo@localhost'
            );
        }

        /**
         * Executes the create operation.
         *
         * - Builds a new virtual host for the given project and hostname.
         * - Prevents duplication if the project already has a host, suggesting alias creation instead.
         * - Registers the default project version after host creation.
         *
         * @return void
         *
         * @throws \InvalidArgumentException If the project already has a host.
         */
        public function execute(): void
        {
            $project = ProjectBuilder::fromMetaData($this->projectName, $this->hostName);
            $vhost = $project->getActiveHost();

            if ($vhost !== null) {
                $message = 'Project "' . $vhost->metadata->projectName . '" has a host "';
                $message .= $vhost->metadata->hostName . '". Create an alias instead.';
                throw new \InvalidArgumentException($message);
            } else {
                $project->vhost->build(fn($s) => $this->registry->addResult($s));
            }

            $version = ProjectVersionBuilder::fromProjectName(
                    $this->projectName,
                    ProjectBuilder::DEFAULT_VERSION_NUMBER
            );
            $version->registerVersion(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project
         *   and enter a host name.
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
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->hostName = ConsoleIO::read('Enter a host name:', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->hostName = HostName::create($values[1]);
            }
            return $this->projectName . self::SEPARATOR . $this->hostName;
        }
    }

}
