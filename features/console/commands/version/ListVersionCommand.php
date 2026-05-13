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
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class ListVersionCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:version', 'project_name', 'Show all project versions from an existing project', 'blog');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromName($this->projectName);
            $versions = $project->getVersions();
            if (!$versions->valid()) {
                throw new \InvalidArgumentException('No version found for project "' . $this->projectName . '"');
            }
            $this->registry->addResult(Formatter::formatSentence('PROJECT', 'STATUS', separator: ' '));
            foreach ($versions as $key => $version) {
                $status = $version->configExists() ? 'OK' : 'No config file';
                $message = ($key + 1) . '. ' . $this->projectName . '[' . $version->versionNumber . ']';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project yo want to delete from?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('Atleast one argument is required.');
                }
                $this->projectName = ResourceName::create($args[0])->shortName;
            }
            return $this->projectName;
        }
    }

}
