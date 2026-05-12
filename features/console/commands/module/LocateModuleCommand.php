<?php

/**
 * Description of LocateModuleCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class LocateModuleCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('locate:module', 'module_name@version_name@project_name', 'Show the full path to an existing project module', 'posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $module = new ModuleBuilder($this->moduleName, $version);
            $module->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::read('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                self::validateHostName($values[0]);
                self::validateHostName($values[1]);
                self::validateHostName($values[2]);
                $this->moduleName = $values[0];
                $this->projectVersion = $values[1];
                $this->projectName = $values[2];
            }
            return $this;
        }
    }

}
