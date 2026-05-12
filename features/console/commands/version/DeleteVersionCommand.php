<?php

/**
 * Description of DeleteVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class DeleteVersionCommand extends CommandContract
    {

        private readonly string $versionNumber;
        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('delete:version', 'version_number@project_name', 'Delete a project version from an existing project', 'v1@blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $version = new ProjectVersionBuilder($project->vhost, $this->versionNumber);
            $version->delete();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->versionNumber = ConsoleIO::read('What is the project version number to delete?', $this->validIdentifier);
                $this->projectName = ConsoleIO::read('What is the project yo want to delete from?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                $this->versionNumber = ConsoleIO::read('Write again the project version number to delete', fn(string $s) => $s === $values[0]);
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
