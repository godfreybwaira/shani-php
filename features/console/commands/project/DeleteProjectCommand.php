<?php

/**
 * Description of DeleteProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\printer\ConsoleIO;

    final class DeleteProjectCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:project', 'project_name', 'Delete a project and its associated metadata. All project data will be lost', 'blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project->delete(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name to delete?', $this->validIdentifier);
            } else {
                $this->projectName = ConsoleIO::read('Write again the project name to delete', fn(string $s) => $s === $args[0]);
            }
            return $this->projectName;
        }
    }

}
