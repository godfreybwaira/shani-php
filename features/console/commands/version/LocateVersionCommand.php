<?php

/**
 * Command to locate a project version.
 *
 * This command shows the filesystem location of a given project version.
 * It can be executed interactively (via console prompts) or by passing
 * arguments directly in the format "project@version".
 *
 * @author goddy
 * @created May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class LocateVersionCommand extends CommandContract
    {

        /**
         * The name of the project whose version is being located.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number of the project to locate.
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
                    'locate:version',
                    'project_name@version_number',
                    'Show the location of the given project version',
                    'blog@v1'
            );
        }

        /**
         * Executes the locate operation.
         *
         * Uses the {@see ProjectVersionBuilder} to find the project version
         * and display its location.
         *
         * @return void
         */
        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $version->locate();
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project.
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
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
