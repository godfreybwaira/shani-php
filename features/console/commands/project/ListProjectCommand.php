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

    final class ListProjectCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:project', null, 'Show all available projects', null);
        }

        public function execute(): void
        {
            $projects = ProjectBuilder::getAll();
            $this->registry->addResult(Formatter::formatSentence('#. PROJECT[ HOST ]', 'STATUS', separator: ' '));
            foreach ($projects as $key => $project) {
                $status = $project->metadata->hostExists() ? 'OK' : 'No Host';
                $message = ($key + 1) . '. ' . $project->metadata->projectName . '[ ' . $project->metadata->hostName . ' ]';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
