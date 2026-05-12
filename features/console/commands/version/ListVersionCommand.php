<?php

/**
 * Description of ListVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;

    final class ListVersionCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('list:version', 'project_name', 'Show all project versions from an existing project', 'blog');
        }

        public function execute(): void
        {
            echo Formatter::placeCenter('List of Project Versions', underline: true);
            $project = ProjectBuilder::fromName($this->projectName);
            $versions = $project->getVersions();
            $index = 1;
            foreach ($versions as $key => $v) {
                echo Formatter::formatSentence($index++, $key);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project yo want to delete from?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('Atleast one argument is required.');
                }
                self::validateIdentifier($values[0]);
                $this->projectName = $values[0];
            }
            return $this;
        }
    }

}
