<?php

/**
 * Description of CreateVersionCommand
 * @author goddy
 *
 * Created on: May 7, 2026 at 9:04:41 AM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class CreateVersionCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:version', 'project_name@version_number', 'Create a new project version from an existing project', 'blog@v1');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $version->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->versionNumber = ModuleName::create(ConsoleIO::read('What is the project version number?', $this->validIdentifier))->directoryName;
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
