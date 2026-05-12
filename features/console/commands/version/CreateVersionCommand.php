<?php

/**
 * Description of CreateVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateVersionCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('create:version', 'version_number@project_name', 'Create a new project version from an existing project', 'v1@blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $project->vhost->registerVersion($this->projectVersion);
            $version = new ProjectVersionBuilder($project->vhost, $this->projectVersion);
            $version->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                $this->projectVersion = $values[0];
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
