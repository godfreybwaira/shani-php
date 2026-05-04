<?php

/**
 * Description of ListProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
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
