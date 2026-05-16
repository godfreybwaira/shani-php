<?php

/**
 * Command to delete a project.
 *
 * This command removes a project and all its associated metadata.
 * Once executed, all project data will be permanently lost.
 * It can be executed interactively (via console prompts) or by passing
 * the project name directly as an argument.
 *
 * Interactive flow:
 *   - If no arguments are provided, prompts the user to select a project.
 *   - Requires confirmation by retyping the project name before deletion.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class DeleteProjectCommand extends CommandContract
    {

        /**
         * The name of the project to delete.
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
                    'delete:project',
                    'project_name',
                    'Delete a project and its associated metadata. All project data will be lost',
                    'blog'
            );
        }

        /**
         * Executes the delete operation.
         *
         * Uses the {@see ProjectBuilder} to find the project and delete it.
         * The deletion process logs results in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project->delete(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project.
         * - Requires confirmation by retyping the project name before deletion.
         *
         * @param string ...$args The command arguments (project name).
         *
         * @return string|null The confirmed project name to delete.
         */
        public function parse(string ...$args): ?string
        {
            $expectedName = !empty($args[0]) ? $args[0] : (new ResourceSelector())->selectProject();
            $this->projectName = ConsoleIO::read('Write again the project name to delete:', fn(string $s) => $s === $expectedName);
            return $this->projectName;
        }
    }

}
