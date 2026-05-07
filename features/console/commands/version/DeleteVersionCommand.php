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
            $project = new ProjectBuilder($this->projectName);
            $version = new ProjectVersionBuilder($this->versionNumber, $project);
            $version->delete();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->versionNumber = ConsoleIO::input('What is the project version number to delete?', $this->validIdentifier);
                $this->projectName = ConsoleIO::input('What is the project yo want to delete from?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->versionNumber = ConsoleIO::input('Write again the project version number to delete', fn(string $s) => $s === $values[0]);
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
