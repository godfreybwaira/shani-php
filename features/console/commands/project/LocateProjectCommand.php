<?php

/**
 * Command to locate a project.
 *
 * This command shows the filesystem path to an existing project.
 * It can be executed interactively (via console prompts) or by passing
 * the project name directly as an argument.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class LocateProjectCommand extends CommandContract
    {

        /**
         * The name of the project to locate.
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
                    'locate:project',
                    'project_name',
                    'Show the full path to an existing project',
                    'blog'
            );
        }

        /**
         * Executes the locate operation.
         *
         * Uses the {@see ProjectBuilder} to find the project
         * and display its location.
         *
         * @return void
         */
        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project->locate();
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
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('At least one argument is required.');
            } else {
                $this->projectName = ResourceName::create($args[0])->longName;
            }
            return $this->projectName;
        }
    }

}
