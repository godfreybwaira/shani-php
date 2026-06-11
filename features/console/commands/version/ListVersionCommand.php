<?php

/**
 * Command to list all versions of a project.
 *
 * This command retrieves all versions associated with a given project
 * and displays them along with their configuration status. It can be
 * executed interactively (via console prompts) or by passing the project
 * name directly as an argument.
 *
 * @author goddy
 * @created May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class ListVersionCommand extends CommandContract
    {

        /**
         * The name of the project whose versions are being listed.
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
                    'list:version',
                    'project_name',
                    'Show all project versions from an existing project',
                    'blog'
            );
        }

        /**
         * Executes the list operation.
         *
         * Retrieves all versions of the specified project using
         * {@see ProjectBuilder::fromName()}, formats them with their status,
         * and adds the results to the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $versions = $project->getVersions();
            $this->registry->addResult(Formatter::formatSentence('#. PROJECT[ version ]', 'STATUS', separator: ' '));
            foreach ($versions as $key => $version) {
                $status = $version->configExists() ? 'OK' : 'No config file';
                $message = ($key + 1) . '. ' . $this->projectName . '[ ' . $version->versionNumber . ' ]';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project.
         * - If arguments are provided, expects the project name directly.
         *
         * @param string ...$args The command arguments (project name).
         *
         * @return string|null The selected or provided project name.
         *
         * @throws \ArgumentCountError If fewer than one argument is provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('At least one argument is required.');
                }
                $this->projectName = ResourceName::create($args[0])->shortName;
            }
            return $this->projectName;
        }
    }

}
