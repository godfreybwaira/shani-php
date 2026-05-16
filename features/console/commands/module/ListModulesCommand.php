<?php

/**
 * Description of ListModulesCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class ListModulesCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:module', 'project_name@version_number', 'Show all available project modules', 'blog@v1');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $modules = $version->getModules();
            if (!$modules->valid()) {
                throw new \InvalidArgumentException('No module found for version "' . $this->versionNumber . '"');
            }
            foreach ($modules as $key => $module) {
                $this->registry->addResult(Formatter::formatSentence($key + 1, $module->moduleName->directoryName));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->versionNumber = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
