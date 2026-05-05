<?php

/**
 * Description of ListProjectModulesCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;

    final class ListModulesCommand extends CommandContract
    {

        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('module:list', 'project_name', 'Show all available project modules', 'blog');
        }

        public function execute(): void
        {
            echo 'Listing all project modules: ' . $this->projectName . PHP_EOL;
            $project = new ProjectBuilder($this->projectName);
            if (!$project->exists()) {
                echo '[ERROR] Project "' . $this->projectName . '" does not exists.' . PHP_EOL;
                return;
            }
            $modules = $project->getModules();
            foreach ($modules as $key => $module) {
                echo Formatter::formatSentence($key + 1, $module->moduleName);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 1) {
                throw new \ArgumentCountError('Atleast one argument is required.');
            }
            $this->validateIdentifier($values[0]);
            $this->projectName = $values[0];
            return $this;
        }
    }

}
