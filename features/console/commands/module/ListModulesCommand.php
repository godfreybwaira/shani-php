<?php

/**
 * Description of ListProjectModulesCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;

    final class ListModulesCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('list:module', 'version_number@project_name', 'Show all available project modules', 'v1@blog');
        }

        public function execute(): void
        {
            echo 'Listing all project version modules: ' . $this->projectVersion . PHP_EOL;
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            if (!$version->exists()) {
                echo '[ERROR] Project version "' . $this->projectVersion . '" does not exists.' . PHP_EOL;
                return;
            }
            $modules = $version->getModules();
            foreach ($modules as $key => $module) {
                echo Formatter::formatSentence($key + 1, $module->moduleName);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                $this->projectVersion = $values[0];
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
