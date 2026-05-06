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
    use features\console\printer\ConsoleIO;

    final class DeleteProjectCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('delete:project', 'project_name', 'Delete a project and its associated metadata. All project data will be lost', 'blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $project->delete();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name to delete?', $this->validIdentifier);
            } else {
                $this->validateIdentifier($args[0]);
                $this->projectName = ConsoleIO::input('Write again the project name to delete', fn(string $s) => $s === $args[0]);
            }
            return $this;
        }
    }

}
