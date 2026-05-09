<?php

/**
 * Description of ListProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class ListProjectCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('list:project', null, 'Show all available projects', null);
        }

        public function execute(): void
        {
            echo 'Listing all projects' . PHP_EOL;
            $directories = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            foreach ($directories as $key => $projectName) {
                $project = ProjectBuilder::fromName($projectName);
                echo Formatter::formatSentence($key - 1, $projectName . self::SEPARATOR . $project->hostname);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            return $this;
        }
    }

}
