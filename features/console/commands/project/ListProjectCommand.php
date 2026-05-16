<?php

/**
 * Command to list all available projects.
 *
 * This command retrieves all projects and displays them along with their
 * associated host and status. It can be executed without arguments, as it
 * simply lists everything available.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;

    final class ListProjectCommand extends CommandContract
    {

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:project', null, 'Show all available projects', null);
        }

        /**
         * Executes the list operation.
         *
         * Retrieves all projects using {@see ProjectBuilder::getAll()},
         * formats them with their status, and adds the results to the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $projects = ProjectBuilder::getAll();
            $this->registry->addResult(Formatter::formatSentence('#. PROJECT[ HOST ]', 'STATUS', separator: ' '));
            foreach ($projects as $key => $project) {
                $status = $project->metadata->hostExists() ? 'OK' : 'No Host';
                $message = ($key + 1) . '. ' . $project->metadata->projectName . '[ ' . $project->metadata->hostName . ' ]';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        /**
         * Parses command arguments.
         *
         * This command does not require arguments, so parsing always returns null.
         *
         * @param string ...$args Ignored arguments.
         *
         * @return string|null Always null.
         */
        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
