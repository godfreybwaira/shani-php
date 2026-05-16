<?php

/**
 * Description of DeleteVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class DeleteVersionCommand extends CommandContract
    {

        private readonly string $versionNumber;
        private readonly string $projectName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:version', 'project_name@version_number', 'Delete a project version from an existing project', 'blog@v1');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            if ($version->vhost->hasVersion($version->versionNumber)) {
                $message = 'Project version number "' . $version->versionNumber . '" is registered to a host "';
                $message .= $version->vhost->metadata->hostName . '", so cannot be deleted. Please unregister it first.';
                throw new \RuntimeException($message);
            }
            $version->delete(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ConsoleIO::read('Write again the project version number to delete', fn(string $s) => $s === $values[1]);
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
