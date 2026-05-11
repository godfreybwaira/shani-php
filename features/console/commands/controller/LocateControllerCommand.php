<?php

/**
 * Description of LocateControllerCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\controller {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class LocateControllerCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectVersion;
        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('locate:controller', 'module_name@version_number@project_name', 'Show the full path to an existing project controllers', 'posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $module = new ModuleBuilder($this->moduleName, $version);
            $module->locateControllers();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three argument is required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                self::validateIdentifier($values[2]);
                $this->moduleName = $values[0];
                $this->projectVersion = $values[1];
                $this->projectName = $values[2];
            }
            return $this;
        }
    }

}
