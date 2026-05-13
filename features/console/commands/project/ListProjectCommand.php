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
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class ListProjectCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:project', null, 'Show all available projects', null);
        }

        public function execute(): void
        {
            $directories = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            foreach ($directories as $key => $projectName) {
                $project = ProjectBuilder::fromName($projectName);
                $this->registry->addResult(Formatter::formatSentence($key - 1, $projectName . self::SEPARATOR . $project->metadata->hostName));
            }
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
