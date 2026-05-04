<?php

/**
 * Description of LocateProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\ProjectBuilder;
    use features\cli\CommandContract;

    final class LocateProjectCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('locate:project', 'project_name', 'Show the full path to an existing project.', 'blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $project->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) > 1) {
                throw new \ArgumentCountError('Only one argument is required.');
            }
            $this->validateIdentifier($args[0]);
            $this->projectName = $args[0];
            return $this;
        }
    }

}
