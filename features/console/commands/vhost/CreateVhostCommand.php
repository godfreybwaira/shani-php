<?php

/**
 * Description of CreateVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class CreateVhostCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:vhost', 'project_name@hostname', 'Create new virtual host to an existing project with no virtual host', 'demo@localhost');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromMetaData($this->projectName, $this->hostName);
            $vhost = $project->getActiveHost();
            if ($vhost !== null) {
                $message = 'Project "' . $vhost->metadata->projectName . '" has a host "';
                $message .= $vhost->metadata->hostName . '". Create an alias instead.';
                throw new \InvalidArgumentException($message);
            } else {
                $project->vhost->build(fn($s) => $this->registry->addResult($s));
            }
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, ProjectBuilder::DEFAULT_VERSION_NUMBER);
            $version->registerVersion(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->hostName = ConsoleIO::read('Enter a host name:', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->hostName = HostName::create($values[1]);
            }
            return $this->projectName . self::SEPARATOR . $this->hostName;
        }
    }

}
