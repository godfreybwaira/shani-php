<?php

/**
 *
 * @author goddy
 * @created May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class SetVersionCommand extends CommandContract
    {

        /**
         * The name of the project for which the version is being created.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number to create for the project.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'set:version',
                    'project_name@version_number',
                    'Set the default application version',
                    'blog@v1'
            );
        }

        /**
         * Executes the create operation.
         *
         * Set the default application version.
         *
         * @return void
         */
        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $message = $version->vhost->setDefaultVersion($version->versionNumber);
            $this->registry->addResult($message);
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project
         *   and version interactively.
         * - If arguments are provided, expects the format "project@version".
         *   Splits the string into project name and version number.
         *
         * @param string ...$args The command arguments (project@version).
         *
         * @return string|null A string containing "project@version" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
