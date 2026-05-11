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
    use features\console\printer\ConsoleIO;

    final class LocateProjectCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('locate:project', 'project_name', 'Show the full path to an existing project', 'blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project?->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('Atleast one argument is allowed.');
            } else {
                self::validateIdentifier($args[0]);
                $this->projectName = $args[0];
            }
            return $this;
        }
    }

}
