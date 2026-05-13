<?php

/**
 * Description of LocateProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class LocateProjectCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'locate:project', 'project_name', 'Show the full path to an existing project', 'blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project->locate();
        }

        public function parse(string ...$args): string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('Atleast one argument is allowed.');
            } else {
                $this->projectName = ResourceName::create($args[0])->longName;
            }
            return $this->projectName;
        }
    }

}
