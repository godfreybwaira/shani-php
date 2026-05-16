<?php

/**
 * Description of CreateProjectCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\project {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;

    final class CreateProjectCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:project', 'project_name@hostname', 'Create a new main project', 'demo@localhost');
        }

        public function execute(): void
        {
            $project = ProjectBuilder::fromMetaData($this->projectName, $this->hostName);
            $project->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ModuleName::create(ConsoleIO::read('Enter the project name:', $this->validIdentifier))->directoryName;
                $this->hostName = ConsoleIO::read('Enter the host name:', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ModuleName::create($values[0])->directoryName;
                $this->hostName = HostName::create($values[1]);
            }
            return $this->projectName . self::SEPARATOR . $this->hostName;
        }
    }

}
