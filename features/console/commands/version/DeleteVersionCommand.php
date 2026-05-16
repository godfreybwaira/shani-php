<?php

/**
 * Command to delete a project version.
 *
 * This command removes a specific version from an existing project.
 * It ensures that the version is not currently registered to a host
 * before allowing deletion. Can be executed interactively (via console prompts)
 * or by passing arguments directly in the format "project@version".
 *
 * @author goddy
 * @created May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class DeleteVersionCommand extends CommandContract
    {

        /**
         * The version number of the project to delete.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * The name of the project whose version is being deleted.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'delete:version',
                    'project_name@version_number',
                    'Delete a project version from an existing project',
                    'blog@v1'
            );
        }

        /**
         * Executes the delete operation.
         *
         * - Checks if the version is registered to a host.
         * - If registered, throws a {@see \RuntimeException} to prevent deletion.
         * - Otherwise, deletes the version and logs the result in the registry.
         *
         * @return void
         *
         * @throws \RuntimeException If the version is registered to a host.
         */
        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);

            if ($version->vhost->hasVersion($version->versionNumber)) {
                $message = 'Project version number "' . $version->versionNumber . '" is registered to a host "';
                $message .= $version->vhost->metadata->hostName . '", so cannot be deleted. Please unregister it first.';
                throw new \RuntimeException($message);
            }

            $version->delete(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project
         *   and version interactively.
         * - If arguments are provided, expects the format "project@version".
         *   Splits the string into project name and version number, and requires
         *   confirmation before deletion.
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
                $this->versionNumber = ConsoleIO::read(
                        'Write again the project version number to delete',
                        fn(string $s) => $s === $values[1]
                );
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
