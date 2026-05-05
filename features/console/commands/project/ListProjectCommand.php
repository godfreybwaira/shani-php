<?php

/**
 * Description of ListProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class ListProjectCommand extends CommandContract
    {

        public function __construct()
        {
            parent::__construct('project:list', null, 'Show all available projects', null);
        }

        public function execute(): void
        {
            echo 'Listing all projects' . PHP_EOL;
            $projects = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            foreach ($projects as $key => $name) {
                echo Formatter::formatSentence($key - 1, $name);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            return $this;
        }
    }

}
